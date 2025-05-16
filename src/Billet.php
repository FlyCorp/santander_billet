<?php

namespace FlyCorp\SantanderBillet;

use FlyCorp\SantanderBillet\Key;
use FlyCorp\SantanderBillet\Payer;
use FlyCorp\SantanderBillet\Beneficiary;

class Billet {
    private $environment;
    private $nsuCode;
    private $nsuDate;
    private $covenantCode;
    private $bankNumber;
    private $clientNumber;
    private $dueDate;
    private $issueDate;
    private $participantCode;
    private $nominalValue;
    private $payer;
    private $beneficiary;
    private $documentKind;
    private $deductionValue;
    private $paymentType;
    private $key;
    private $writeOffQuantityDays;
    private $messages;

    public function __construct() {
        $this->payer = new Payer();
        $this->beneficiary = new Beneficiary();
        $this->key = new Key();
    }

    public function toArray():array {
        $array = [
            "environment" => $this->environment,
            "nsuCode" => $this->nsuCode,
            "nsuDate" => $this->nsuDate,
            "covenantCode" => $this->covenantCode,
            "bankNumber" => $this->bankNumber,
            "clientNumber" => $this->clientNumber,
            "dueDate" => $this->dueDate,
            "issueDate" => $this->issueDate,
            "participantCode" => $this->participantCode,
            "nominalValue" => $this->nominalValue,
            "payer" => $this->payer->toArray(),
            "beneficiary" => $this->beneficiary->toArray(),
            "documentKind" => $this->documentKind,
            "deductionValue" => $this->deductionValue,
            "paymentType" => $this->paymentType,
            "key" => $this->key->toArray(),
            "messages" => $this->messages,
        ];

        if (isset($this->writeOffQuantityDays)) {
            $array["writeOffQuantityDays"] = $this->writeOffQuantityDays;
        }

        return $array;
    }    

    public function setEnvironment($environment) { $this->environment = $environment; return $this; }
    public function setNsuCode($nsuCode) { $this->nsuCode = $nsuCode; return $this; }
    public function setNsuDate($nsuDate) { $this->nsuDate = $nsuDate; return $this; }
    public function setCovenantCode($covenantCode) { $this->covenantCode = $covenantCode; return $this; }
    public function setBankNumber($bankNumber) { $this->bankNumber = $bankNumber; return $this; }
    public function setClientNumber($clientNumber) { $this->clientNumber = $clientNumber; return $this; }
    public function setDueDate($dueDate) { $this->dueDate = $dueDate; return $this; }
    public function setIssueDate($issueDate) { $this->issueDate = $issueDate; return $this; }
    public function setParticipantCode($participantCode) { $this->participantCode = $participantCode; return $this; }
    public function setNominalValue($nominalValue) { $this->nominalValue = $nominalValue; return $this; }
    public function setDocumentKind($documentKind) { $this->documentKind = $documentKind; return $this; }
    public function setDeductionValue($deductionValue) { $this->deductionValue = $deductionValue; return $this; }
    public function setPaymentType($paymentType) { $this->paymentType = $paymentType; return $this; }
    public function setWriteOffQuantityDays($writeOffQuantityDays) { $this->writeOffQuantityDays = $writeOffQuantityDays; return $this; }
    public function setMessages($messages) { $this->messages = $messages; return $this; }
    
    public function payer() { return $this->payer; }
    public function beneficiary() { return $this->beneficiary; }
    public function key() { return $this->key; }
}