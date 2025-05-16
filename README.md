# Criar boleto pix Santander

Pacote responsável por gerar boletos com opção PIX via API do Santander

## Índice

1.  [🚀 Começando](#rocket-começando)
    * [1.1. 📋 Pré-requisitos](#clipboard-pré-requisitos)
    * [1.2. 🔧 Instalação](#wrench-instalação)
2.  [Variáveis de ambiente(Opcional)](#variáveis-de-ambienteopcional)
3.  [Instanciar classe Santander](#instanciar-classe-santander)
    * [3.1. Usando configuração padrão (do arquivo de configuração)](#usando-configuração-padrão-do-arquivo-de-configuração)
    * [3.2. Usando configuração personalizada(opcional)](#usando-configuração-personalizadaopcional)
4.  [Endpoints de API](#endpoints-de-api)
    * [4.1. Workspace:](#workspace)
    * [4.2. Boletos:](#boletos)
    * [4.3. Consulta simples:](#consulta-simples)

---

1.  ## 🚀 Começando

    Essas instruções permitirão que você obtenha uma cópia do projeto em operação na sua máquina local para fins de desenvolvimento e teste.

    1.1. ### 📋 Pré-requisitos

    -   PHP ˆ7.2
    -   Laravel ˆ6.0
    -   Composer

    1.2. ### 🔧 Instalação

    Use o gerenciador de pacotes COMPOSER para incluir as dependéncias ao seu projeto.

    Comando a executar:

    ```bash
    composer require flycorp/santander-billet
    ```

    Ou, em seu `composer.json` adicione:

    ```json
    {
        "require": {
            "flycorp/santander-billet": "dev-main"
        }
    }
    ```

    Em seguida, execute o comando de instalação do gerenciador de pacotes:

    ```bash
    composer install
    ```

    Em seguida, publique o arquivo de configuração do pacote em `config\santander_billet.php`:

    ```bash
    php artisan vendor:publish --provider="FlyCorp\SantanderBillet\providers\SantanderBilletServiceProvider" --tag=config
    ```

    Lembre-se de realizar a importação das classes quando for utilizar o pacote:

    ```php
    #Classe principal para executar chamadas de api
    use FlyCorp\SantanderBillet\Santander;
    #Classe utilizada exclusivamente na parametrização do boleto
    use FlyCorp\SantanderBillet\Billet;
    ```

2.  ## Variáveis de ambiente(Opcional)

    Adicione ao seu arquivo `.env` as variáveis de ambiente que possibilitarão a comunicação com o respectivo endpoint de API:

    ```env
    SANTANDER_BILLET_ENVIRONMENT="sandbox"
    SANTANDER_BILLET_CLIENT_ID=
    SANTANDER_BILLET_CLIENT_SECRET=
    SANTANDER_BILLET_CERTIFICATE_AUTH=
    SANTANDER_BILLET_CERTIFICATE_PATH=
    ```

    Descrição das variáveis:

    -   `SANTANDER_BILLET_ENVIRONMENT`: Ambiente da API(sandbox ou production)
    -   `SANTANDER_BILLET_CLIENT_ID`: Valor do client_id fornecido pelo Santader
    -   `SANTANDER_BILLET_CLIENT_SECRET`: Valor do client_secret fornecido pelo Santander
    -   `SANTANDER_BILLET_CERTIFICATE_AUTH`: Senha do certificado
    -   `SANTANDER_BILLET_CERTIFICATE_PATH`: Caminho do certificado em formato pfx dentro da pasta Storage

3.  ## Instanciar classe Santander

    3.1. ### Usando configuração padrão (do arquivo de configuração)
    ```php
    use FlyCorp\SantanderBillet\Santander;

    $santanderDefault = new Santander();
    ```

    3.2. ### Usando configuração personalizada(opcional)
    ```php
    use FlyCorp\SantanderBillet\Santander;

    $customConfig = [
        'host' => '[https://api-sandbox.santander.com.br](https://api-sandbox.santander.com.br)',
        'certificate_path' => 'certs/custom_cert.p12',
        'certificate_auth' => 'senha123',
        'client_id' => 'meu-client-id',
        'client_secret' => 'meu-client-secret'
    ];
    $santanderCustom = new Santander($customConfig);
    ```

4.  ## Endpoints de API

    4.1. ### Workspace:

    4.1.1. [POST] Criar workspace */collection_bill_management/v2/workspaces*:

    ```php
    $santanderIntegration = new Santander;

    $workspace = [
        "type" => "BILLING",
        "covenants" => [
            [
                "code" => 0000001
            ]
        ],
        "description" => "Testando",
        "bankSlipBillingWebhookActive" => true,
        "pixBillingWebhookActive" => true,
        "webhookURL" => "https://teste"
    ];

    $data = $santanderIntegration->createWorkspace($workspace);
    ```

    4.1.2. [GET] Consultar workspace */collection_bill_management/v2/workspaces*:

    ```php
    $santanderIntegration = new Santander;

    $data = $santanderIntegration->searchWorkspace(); #INFORME O ID DO WORKSPACE PARA PESQUISA ESPECÍFICA
    ```

    4.1.3. [DELETE] Deletar workspace */collection_bill_management/v2/workspaces/{workspace_id}*:

    ```php
    $santanderIntegration = new Santander;

    $data = $santanderIntegration->deleteWorkspace("5ca21612-ee50-4626-b2da-e66ca35c605f");
    ```

    4.1.4. [PATCH] Alterar workspace */collection_bill_management/v2/workspaces/{workspace_id}*:

    ```php
    $santanderIntegration = new Santander;

    $body = [
        "covenants" => [
            [
                "code" => 0000001
            ]
        ],
        "description" => "Testando"
    ];

    #Forneça o workspace_id e body da requisição
    $data = $santanderIntegration->updateWorkspace("5ca21612-ee50-4626-b2da-e66ca35c605f", $body);
    ```

    4.2. ### Boletos:

    4.2.1. [POST] Registrar boleto */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips*:

    4.2.1.1 Configurando boleto como array:
    ```php
    $billet = new Billet();
    $santanderIntegration = new Santander;

    $billet = [
        "environment" => "sandbox",
        "workspaceId"=> 201,
        "nsuCode" => 1014,
        "nsuDate" => "2023-05-09",
        "covenantCode" => '0000001',
        "bankNumber" => "1014",
        "clientNumber" => "123",
        "dueDate" => "2023-05-09",
        "issueDate" => "2023-05-09",
        "participantCode" => "teste liq abat",
        "nominalValue" => number_format(1.00, 2, '.', ''),
        "payer" => [
            "name" => "Fulano",
            "documentType" => "CPF",
            "documentNumber" => "94620639079",
            "address" => "rua nove de janeiro",
            "neighborhood" => "bela vista",
            "city" => "sao paulo",
            "state" => "SP",
            "zipCode" => "05134-897"
        ],
        "beneficiary" => [
            "name" => "Ciclano",
            "documentType" => "CNPJ",
            "documentNumber" => "20201210000155",
        ],
        "documentKind" => "DUPLICATA_MERCANTIL",
        "deductionValue" => "0.10",
        "paymentType" => "REGISTRO",
        "key" => [
            "type" => "CNPJ",
            "dictKey" => "00000000000000"
        ],
        "writeOffQuantityDays" => "30",
        "messages" => [
            "mensagem um",
            "mensagem dois"
        ]
    ];

    #Forneça o workspace_id e body da requisição
    $data = $santanderIntegration->registerBill('5ca21612-ee50-4626-b2da-e66ca35c605f', $billet);
    ```

    4.2.1.2 Configurando boleto como objeto:
    ```php
    $billet = new Billet();
    $santanderIntegration = new Santander;

    $billet->setEnvironment("sandbox")# Valores aceitos(sandbox, PRODUCAO)
        ->setNsuCode(1014)
        ->setNsuDate("2023-05-09")
        ->setCovenantCode("0000001")#INFORME O SEU
        ->setBankNumber("1014")#INFORME O SEU
        ->setClientNumber("123")
        ->setDueDate("2023-05-09")
        ->setIssueDate("2023-05-09")
        ->setParticipantCode("teste liq abat")
        ->setNominalValue(1.00)
        ->payer()
            ->setName("Fulano")
            ->setDocumentType("CPF")
            ->setDocumentNumber("94620639079")
            ->setAddress("rua nove de janeiro")
            ->setNeighborhood("bela vista")
            ->setCity("sao paulo")
            ->setState("SP")
            ->setZipCode("05134-897")
        ->beneficiary()
            ->setName("Ciclano")
            ->setDocumentType("CNPJ")
            ->setDocumentNumber("20201210000155")
        ->setDocumentKind("DUPLICATA_MERCANTIL")
        ->setDeductionValue(0.10)
        ->setPaymentType("REGISTRO")
        ->key()#ESTA CHAVE É NECESSÁRIA PARA GERAR O QR-CODE
            ->setType("CNPJ")#TIPOS ACEITOS (CPF, CNPJ, CELULAR, EMAIL, EVP)
            ->setDictKey("00000000000000")#CNPJ SEM MÁSCARA
        ->setWriteOffQuantityDays("30")
        ->setMessages(["mensagem um", "mensagem dois"]);

    #Forneça o workspace_id e body da requisição
    $data = $santanderIntegration->registerBill('5ca21612-ee50-4626-b2da-e66ca35c605f', $billet->toArray());
    ```

    4.2.2. [PATCH] Atualizar instruções */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips*:

    ```php
    $santanderIntegration = new Santander;

    $body = [
        "covenantCode" => "0000001",
        "bankNumber" => "123",
        "operation" => "BAIXAR"
    ];

    #Forneça o workspace_id e body da requisição
    $data = $santanderIntegration->updateBillInstructions("5ca21612-ee50-4626-b2da-e66ca35c605f", $body);
    ```

    4.2.3. [POST] Obter dados para acessar boleto(PDF/QR-code) */collection_bill_management/v2/bills/{$billId}/bank_slips*:

    ```php
    $santanderIntegration = new Santander;

    $billId = "0000001.1005";

    $body = [
        "payerDocumentNumber" => "94620639079",
    ];

    #Forneça o bill_id e body da requisição
    $data = $santanderIntegration->getPdfBill($billId, $body);
    ```

    4.3. ### Consulta simples:

    4.3.1. [GET] Consulta sonda */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips/{bank_slips}*:

    ```php
    $santanderIntegration = new Santander;

    $data = $santanderIntegration->simpleSearchBySonda();
    ```

    4.3.2. [GET] Consulta nn */collection_bill_management/v2/bills*:

    ```php
    $santanderIntegration = new Santander;

    $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
    $bankNumber = "10054325"; #CÓDIGO DO BANCO

    $data = $santanderIntegration->detailedSearchByNn($beneficiaryCode, $bankNumber);
    ```

    4.3.3. [GET] Consulta sn */collection_bill_management/v2/bills*:

    ```php
    $santanderIntegration = new Santander;

    $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
    $bankNumber = "10054325"; #CÓDIGO DO BANCO
    $dueDate = "2023-04-25";
    $nominalValue = "100.00";

    $data = $santanderIntegration->detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue);
    ```

    4.3.4. [GET] Consulta sn */collection_bill_management/v2/bills*:

    ```php
    $santanderIntegration = new Santander;

    $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
    $bankNumber = "10054325"; #CÓDIGO DO BANCO
    $dueDate = "2023-04-25"; #DATA DE VENCIMENTO
    $nominalValue = "100.00";#VALOR NOMINAL

    $data = $santanderIntegration->detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue);
    ```

    4.3.5. [GET] Pesquisa por tipo */collection_bill_management/v2/bills*:

    ```php
    $santanderIntegration = new Santander;

    $billId = "0000001.1005";

    #Valores para type (bankslips, default, duplicate, registry, settlement)
    #A. default: Pesquisa padrão, trazendo somente dados básicos do boleto
    #B. duplicate: Pesquisa de dados para emissão de segunda via de boleto
    #C. bankslip: Pesquisa para dados completos do boleto
    #D. setlement: Pesquisa para informações de baixas/liquidações do boleto
    #E. registry: Pesquisa de informações de cartório no boleto
    $data = $santanderIntegration->detailedSearchBySearchType($billId, $type);
    ```