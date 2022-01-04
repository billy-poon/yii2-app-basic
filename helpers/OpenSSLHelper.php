<?php

namespace app\helpers;

class OpenSSLHelper extends \yii\base\BaseObject
{
  // openssl genrsa -out private.key 1024
  // openssl pkcs8 -topk8 -inform PEM -in private.key -outform PEM -nocrypt -out private.pem
  // openssl rsa -in private.pem -pubout -out public.pem

  // cat private.pem
  const PIKEY = <<<PIKEY
-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALZEXZDSTHxnzmSn
d2qDikfctBNxLAfEkgwWRIHx5rfLWKx3TfHNKPKdVp9TfxTAv/kSRb+u2nOP+F5o
1BJltBW06+DQfhnOdlBHMK1uvNdts6hKklvEgtNIw4/C09/Uqy/ROwJfZKwPY6GE
56WpE4tagCCbJMeu/iz7GgKtk40zAgMBAAECgYAuh/eHEFkcbXfgdGxlEd3MiMik
KgE+nm1WjpzAu9vV0iD6Lp8EewbYIVjK5gsMZkTcNlD+JYp5dCjJGWQCqlo1vctr
o+paSGkRvmh6UxYx7L5alv8Oz6shGO3cWpEU9NSRR1a5fBPDh9/bC6Vg2/oRLChc
+0ZTk45ldR6B6D+LuQJBAOoUSCvizcUtILrjbCNtGuuGiHWQ4RDEXzmLz1n1tSqz
E6retKf9Qy8J3pKTKaNtla00x12jgT4pG+2ZrwiAZh8CQQDHVfXGUJBvYjc4yS5z
OUJU2cJ9sE+OwzNCUtR2bt/7PWAp+LZkpxU3ZEovFWQWAZBpcRgIf3D6qd8xw9lw
oa5tAkBkgDTEcup6H/gPhZlmVG/cc7SfFYcsVcO0x2xNaYtRO/XTxS63eaugxJIF
SJ32BxTeeuymLY9OCwRsrTFTax1tAkBAIbLSHAdsHoA/v9I29fwWSn0dbQUbnEe4
leePNvrO3R88Qa2E0pCr4pNPdKVfwx8QHXeq/D2AF/kcDLO/XfU9AkEAzsIk+NQH
JQmUSpmOgkTKb3DJEV/gY4NWngtUKozpKYMldJYZxobpwO8YxrHTQs665a9tHRun
8LhCHgIhqwSOXg==
-----END PRIVATE KEY-----
PIKEY;

  // cat public.pem
  const PUKEY = <<<PUKEY
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2RF2Q0kx8Z85kp3dqg4pH3LQT
cSwHxJIMFkSB8ea3y1isd03xzSjynVafU38UwL/5EkW/rtpzj/heaNQSZbQVtOvg
0H4ZznZQRzCtbrzXbbOoSpJbxILTSMOPwtPf1Ksv0TsCX2SsD2OhhOelqROLWoAg
myTHrv4s+xoCrZONMwIDAQAB
-----END PUBLIC KEY-----
PUKEY;

  const AES_128_CBC = 'AES-128-CBC';

  /**
   * @return string[] [$pubkey, $privkey] in RAW format
   */
  public static function create_rsa_key_pair($private_key_bits = 1024, $private_key_type = OPENSSL_KEYTYPE_RSA)
  {
    $config = compact('private_key_bits', 'private_key_type');
    if ($configFile = realpath(\Yii::getAlias('@app/assets/openssl.cnf'))) {
      $config['config'] = $configFile;
    }

    if ($key = @openssl_pkey_new($config)) {
      if (@openssl_pkey_export($key, $privkey, null, $config)) {
        if ($details = @openssl_pkey_get_details($key)) {
          if ($pubkey = $details['key']) {
            return [$pubkey, $privkey];
          }
        }
      }
    }

    return null;
  }

  /**
   * AES encrypt
   * @param string $data RAW data to be encrypted
   * @param string $key in|out
   * @param string $iv in|out
   * @param string $method in|out, AES-128-CBC if not specified
   */
  public static function encrypt($data, &$key = null, &$iv = null, &$method = null)
  {
    if ($method === null) $method = static::AES_128_CBC;

    if ($key === null) {
      $key = openssl_random_pseudo_bytes(16);
    }

    if ($iv === null) {
      $len = openssl_cipher_iv_length($method);
      $iv = openssl_random_pseudo_bytes($len);
    }

    if ($encrypted = @openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv)) {
      return $encrypted;
    }

    return null;
  }

  /**
   * AES decrypt
   * @param string $data RAW data to be decrypted
   * @param string $key
   * @param string $iv
   * @param string $method in|out, AES-128-CBC if not specified
   */
  public static function decrypt($data, $key, $iv, &$method = null)
  {
    if ($method === null) $method = static::AES_128_CBC;

    if ($data = @openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv)) {
      return $data;
    }

    return null;
  }

  private static $_instances = [];

  /**
   * Get or create instance
   * @param string $pubkey public key, PEM format
   * @param string $privkey private key, PEM format
   * @return self
   */
  public static function instance($pubkey = null, $privkey = null)
  {
    if (!$pubkey || !$privkey) {
      $pubkey = static::PUKEY;
      $privkey = static::PIKEY;
    }

    $hash = sha1("{$pubkey}#{$privkey}");
    if (isset(static::$_instances[$hash]) || array_key_exists($hash, static::$_instances)) {
      return static::$_instances[$hash];
    }

    $pukey = @openssl_pkey_get_public($pubkey);
    $pikey = @openssl_pkey_get_private($privkey);

    return new static($pukey, $pikey, $hash);
  }

  private $_pukey;
  private $_pikey;

  /**
   * Constructor
   * @param resource $pukey
   * @param resource $pikey
   * @param string $hash
   */
  public function __construct($pukey, $pikey, $hash)
  {
    $this->_pukey = $pukey;
    $this->_pikey = $pikey;
    $this->_hash = $hash;
  }

  private $_hash;
  public function getHash() : ?string
  {
    return $this->_hash;
  }

  public function sign($data) : bool
  {
    if (@openssl_sign($data, $signature, $this->_pikey)) {
      return $signature;
    }

    return false;
  }

  public function verify($data, $signature) : bool
  {
    if (@openssl_verify($data, $signature, $this->_pukey)) {
      return true;
    }

    return false;
  }

  /**
   * Encrypt using private key
   * @param string $data RAW data
   * @return string|false
   */
  public function encrypt_private($data)
  {
    $encrypted = '';
    if (@openssl_private_encrypt($data, $encrypted, $this->_pikey)) {
      return $encrypted;
    }

    return false;
  }

  /**
   * Decrypt using private key
   * @param string $data RAW data
   * @return string|false
   */
  public function decrypt_private($data)
  {
    if (@openssl_private_decrypt($data, $decrypted, $this->_pikey)) {
      return $decrypted;
    }

    return false;
  }

  /**
   * Encrypt using public key
   * @param string $data RAW data
   * @return string|false
   */
  public function encrypt_public($data)
  {
    if (@openssl_public_encrypt($data, $encrypted, $this->_pukey)) {
      return $encrypted;
    }

    return false;
  }

  /**
   * Decrypt using public key
   * @param string $data RAW data
   * @return string|false
   */
  public function decrypt_public($data)
  {
    if (@openssl_public_decrypt($data, $decrypted, $this->_pukey)) {
      return $decrypted;
    }

    return false;
  }

  public function encrypt_private_long($data, $method = null, $key = null)
  {
    $encrypted = static::encrypt($data, $key, $iv, $method);

    $ivcode = base64_encode($iv);
    $keycode = base64_encode($this->encrypt_private($key));
    $datacode = base64_encode($encrypted);

    return "{$ivcode}#{$keycode}#{$datacode}";
  }

  public function decrypt_public_long($data, $method = null)
  {
    list($ivcode, $keycode, $datacode) = explode('#', $data);

    $iv = base64_decode($ivcode);
    $key = $this->decrypt_public(base64_decode($keycode));
    $data = base64_decode($datacode);

    return static::decrypt($data, $key, $iv, $method);
  }

  public function destroy()
  {
    @openssl_free_key($this->_pukey);
    @openssl_free_key($this->_pikey);
    $this->_pukey = null;
    $this->_pikey = null;

    if ($hash = $this->hash) {
      unset(static::$_instances[$hash]);
    }
  }
}
