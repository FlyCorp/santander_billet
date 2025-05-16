<?php

namespace FlyCorp\SantanderBillet;

use GuzzleHttp\Client;

class Santander
{
    protected $client, $options, $config;

    /**
     * Construtor da classe Santander para integração com o serviço de boletos.
     * 
     * Permite a configuração dinâmica da conexão com a API do Santander, podendo receber
     * parâmetros personalizados ou usar a configuração padrão do Laravel.
     *
     * @param array|null $config Array de configuração personalizada. Se null, usa a configuração padrão.
     *                           Deve conter as seguintes chaves:
     *                           - 'host': string - URL base da API do Santander
     *                           - 'certificate_path': string - Caminho relativo (a partir de storage/) para o certificado P12/PEM
     *                           - 'certificate_auth': string - Senha do certificado P12
     *                           - 'client_id': string - Client ID para autenticação OAuth
     *                           - 'client_secret': string - Client Secret para autenticação OAuth
     *                           - 'ssl_key': string - Caminho relativo (a partir de storage/) para o arquivo contendo a chave SSL (opcional)
     * @param array|null $curlOptions Opções personalizadas para cURL. Se null, usa as opções padrão.
     *                               Deve incluir CURLOPT_SSLCERTTYPE para especificar o tipo de certificado.
     * 
     * @example 
     * // Usando configuração padrão (do arquivo de configuração)
     * new Santander();
     * 
     * @example
     * // Usando configuração personalizada
     * new Santander([
     *     'host' => 'https://api-sandbox.santander.com.br',
     *     'certificate_path' => 'certs/meu_certificado.pfx',
     *     'certificate_auth' => 'minha_senha_secreta',
     *     'client_id' => 'meu-client-id-123',
     *     'client_secret' => 'meu-client-secret-456'
     * ], [
     *     CURLOPT_SSLCERTTYPE => 'P12'
     * ]);
     * 
     * @throws \RuntimeException Se alguma configuração obrigatória estiver faltando
     * @throws \InvalidArgumentException Se CURLOPT_SSLCERTTYPE não estiver nas opções cURL
     */
    public function __construct(array $config = null, array $curlOptions = [CURLOPT_SSLCERTTYPE => 'P12'])
    {
        // Usa configuração padrão do arquivo de configuração se nenhuma for fornecida
        $this->config = $config ?? config('santander_billet.integrations');
        
        // Valida as configurações mínimas necessárias
        $requiredKeys = ['host', 'certificate_path', 'certificate_auth', 'client_id', 'client_secret'];
        foreach ($requiredKeys as $key) {
            if (!isset($this->config[$key])) {
                throw new \RuntimeException("Configuração obrigatória '$key' não encontrada");
            }
        }

        $certificatePath = storage_path($this->config['certificate_path']);

        
        // Valida se CURLOPT_SSLCERTTYPE está presente
        if (!array_key_exists(CURLOPT_SSLCERTTYPE, $curlOptions)) {
            throw new \InvalidArgumentException("A opção CURLOPT_SSLCERTTYPE é obrigatória nas configurações cURL");
        }

        $clientConfig = [
            'base_uri' => $this->config['host'],
            'curl' => $curlOptions,
        ];
        // Adiciona a chave SSL se estiver configurada caso o formato do certificado não seja P12 e sim PEM
        if($curlOptions[CURLOPT_SSLCERTTYPE] !== 'P12' && isset($this->config['ssl_key'])) {
            $clientConfig['cert'] = $certificatePath;
            $clientConfig['ssl_key'] = storage_path($this->config['certificate_auth']);
        }else{
            $clientConfig['cert'] = [$certificatePath, $this->config['certificate_auth']];
        }

        // Inicializa o cliente HTTP com as configurações de certificado
        $this->client = new Client($clientConfig);

        // Configura as opções padrão para autenticação OAuth
        $this->options = [
            'form_params' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'client_credentials'
            ]
        ];
    }

    private function handleRequest($method, $uri, $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);

            $body = $response->getBody();
            $data = json_decode($body, true);

            return [
                'success' => true,
                'code' => $response->getStatusCode(),
                'data' => $data
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $data = json_decode($responseBody, true);

            return [
                'success' => false,
                'code' => $e->getResponse()->getStatusCode(),
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }


    private function retrieveToken()
    {
        return $this->handleRequest('POST', 'auth/oauth/v2/token', $this->options);
    }

    private function token()
    {
        $cached = cache()->get('SANTANDER_BILLET_RESPONSE');

        return $cached
        ? $cached
        :cache()->remember('SANTANDER_BILLET_RESPONSE', now()->addSeconds(900), function(){

            $response = self::retrieveToken();

            if($response['success']){
                return $response['data'];
            }

            throw new \Exception($response['message'], $response['code']);
        });
    }

    private function authorizeHeaders()
    {
        $tokenData = self::token();

        return [
            "X-Application-Key" => config('santander_billet.integrations.client_id'),
            "Authorization" => "Bearer {$tokenData['access_token']}",
        ];
    }

    public function createWorkspace($body)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('POST', 'collection_bill_management/v2/workspaces', $options);
    }

    public function searchWorkspace($workspaceId = null)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('GET', $workspaceId ? "collection_bill_management/v2/workspaces/{$workspaceId}" : "collection_bill_management/v2/workspaces", $options);
    }

    public function deleteWorkspace($workspaceId)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('DELETE', "collection_bill_management/v2/workspaces/{$workspaceId}", $options);
    }

    public function updateWorkspace($workspaceId, $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('PATCH', "collection_bill_management/v2/workspaces/{$workspaceId}", $options);
    }

    public function registerBill($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('POST', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);
    }

    public function updateBillInstructions($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('PATCH', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);
    }

    public function getPdfBill($billId, $body)
    {
        #body["payerDocumentNumber"] => Documento do pagador (CPF/CNPJ)
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('POST', "collection_bill_management/v2/bills/{$billId}/bank_slips", $options);
    }

    public function simpleSearchBySonda($workspaceId, $bankSlip)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips/{$bankSlip}", $options);
    }

    public function detailedSearchByNn($beneficiaryCode, $bankNumber)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "beneficiaryCode" => $beneficiaryCode,
                "bankNumber" => $bankNumber,
            ],
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/bills", $options);
    }

    public function detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "beneficiaryCode" => $beneficiaryCode,
                "bankNumber" => $bankNumber,
                "dueDate" => $dueDate,
                "nominalValue" => $nominalValue,
            ],
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/bills", $options);
    }

    public function detailedSearchBySearchType($billId, $searchType)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "tipoConsulta" => $searchType
            ],
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/bills/{$billId}", $options);
    }
}
