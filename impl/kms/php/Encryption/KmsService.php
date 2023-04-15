<?php
use Aws\Kms\KmsClient;

class KmsEncryptionService
{
    private $kmsClient;
    private $keyId;

    public function __construct($region, $accessKeyId, $secretAccessKey, $keyId)
    {
        $this->kmsClient = new KmsClient([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => $accessKeyId,
                'secret' => $secretAccessKey,
            ],
        ]);
        $this->keyId = $keyId;
    }

    public function encrypt($data)
    {
        $result = $this->kmsClient->encrypt([
            'KeyId' => $this->keyId,
            'Plaintext' => $data,
        ]);        
        return $result['CiphertextBlob'];
    }

    public function encryptFile($inputFile, $outFile = "") {
        $inputFile = dirname(dirname(__DIR__)).'/'.$inputFile;
        if(file_exists($inputFile)) {
            $content = file_get_contents($inputFile);
            $encryptedData = $this->encrypt($content);
            $outFile = $outFile != "" ? $outFile : $inputFile;
            file_put_contents($outFile.'.encrypted', $encryptedData);
            return "Encrypted";
        } else {
            throw new Exception("File doesn't exists");
        }
    }

    public function decrypt($encryptedData)
    {
        $result = $this->kmsClient->decrypt([
            'CiphertextBlob' => $encryptedData,
        ]);
        return $result['Plaintext'];
    }

    public function decryptFile($encryptedFile) {
        $inputFile = dirname(dirname(__DIR__)).'/'.$encryptedFile.'.encrypted';
        if(file_exists($inputFile)) {
            $encrypted = file_get_contents($inputFile);
            $decryptedData = self::decrypt($encrypted);
            // $decryptedData = openssl_decrypt($encrypted, 'aes256', $dataKey, OPENSSL_RAW_DATA);
            echo $decryptedData;
            return $decryptedData;
        } else {
            throw new Exception("File doesn't exists");
        }
    }
}
?>