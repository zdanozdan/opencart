<?php

/*
 * Created by tpay.com.
 * Date: 13.06.2017
 * Time: 16:14
 */

namespace tpayLibs\src\_class_tpay;


use tpayLibs\src\_class_tpay\Utilities\TException;
use tpayLibs\src\Dictionaries\FieldsConfigDictionary;
use tpayLibs\src\Dictionaries\PaymentTypesDictionary;

class PaymentBlik extends TransactionApi
{
    public function handleBlikPayment($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            throw new TException('Invalid or empty input parameters');
        }
        if (isset($params[FieldsConfigDictionary::CODE]) && !isset($params[static::ALIAS])) {
            $params[FieldsConfigDictionary::CODE] = (int)$params[FieldsConfigDictionary::CODE];
            $response = $this->handleBlik(PaymentTypesDictionary::PAYMENT_TYPE_BLIK_T6STANDARD, $params);
        } elseif (isset($params[FieldsConfigDictionary::CODE]) && isset($params[static::ALIAS])) {
            $params[FieldsConfigDictionary::CODE] = (int)$params[FieldsConfigDictionary::CODE];
            $response = $this->handleBlik(PaymentTypesDictionary::PAYMENT_TYPE_BLIK_T6REGISTER, $params);
        } else {
            $response = $this->handleBlik(PaymentTypesDictionary::PAYMENT_TYPE_BLIK_ALIAS, $params);
        }

        switch ($response['result']) {
            case 1:
                $success = true;
                break;
            case 0:
                if (isset($response[static::ERR]) && $response[static::ERR] === 'ERR82') {
                    $apps = array();
                    foreach ($response['availableUserApps'] as $key => $value) {
                        $apps[] = get_object_vars($value);
                    }
                    return $apps;
                } else {
                    $success = false;
                }
                break;
            default:
                $success = false;
                break;
        }
        return $success;
    }

    public function handleBlik($type, $params)
    {
        $params = $this->validateConfig($type, $params);

        switch ($type) {
            case PaymentTypesDictionary::PAYMENT_TYPE_BLIK_T6STANDARD:
                $response = $this->blik($params[static::TITLE], $params[FieldsConfigDictionary::CODE]);
                break;
            case PaymentTypesDictionary::PAYMENT_TYPE_BLIK_T6REGISTER:
                $response = $this->blik($params[static::TITLE], $params[FieldsConfigDictionary::CODE],
                    $params[static::ALIAS]);
                break;
            case PaymentTypesDictionary::PAYMENT_TYPE_BLIK_ALIAS:
                $response = $this->blik($params[static::TITLE], '', $params[static::ALIAS]);
                break;
            default:
                throw new TException('Undefined transaction type!');
        }
        return $response;
    }

    public function blik($title, $code = '', $alias = '')
    {
        if (empty($title) || !is_string($title)) {
            throw new TException('Transaction title is empty or invalid');
        }
        $config['title'] = $title;
        if (!empty($code)) {
            $config[FieldsConfigDictionary::CODE] = $code;
        }
        if (!empty($alias)) {
            $config[static::ALIAS] = $alias;
        }

        $url = $this->apiURL . $this->trApiKey . '/transaction/blik';

        return $this->requests($url, $config);
    }

}
