<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Encryption
{
    private $method = 'AES-256-CBC';
    private $key;

    public function __construct()
    {
        // Use AUTH_SALT as encryption key if available, otherwise fallback (should be available in WP)
        $this->key = defined('AUTH_SALT') ? AUTH_SALT : 'cri-crm-fallback-secret-salt-change-me';

        // Hook for Gemini Key
        add_filter('pre_update_option_cri_crm_gemini_key', array($this, 'encrypt_option'), 10, 2);
        add_filter('option_cri_crm_gemini_key', array($this, 'decrypt_option'));

        // Hook for Brevo Key (Might as well encrypt this too for consistency)
        add_filter('pre_update_option_cri_crm_brevo_key', array($this, 'encrypt_option'), 10, 2);
        add_filter('option_cri_crm_brevo_key', array($this, 'decrypt_option'));
    }

    /**
     * Encrypt value before saving to DB
     */
    public function encrypt_option($new_value, $old_value = '')
    {
        if (empty($new_value)) {
            return $new_value; // Don't encrypt empty
        }

        // If it looks like it's already encrypted (starts with base64 iv prefix logic?), skip. 
        // But pre_update receives the raw input.
        // We assume input is plain text from the Settings Form.

        return $this->encrypt($new_value);
    }

    /**
     * Decrypt value when retrieving from DB
     */
    public function decrypt_option($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Attempt decrypt
        $decrypted = $this->decrypt($value);

        // If decryption failed (returns false) or result is empty, it might be an old plain text value.
        // Return original if decryption fails (Backward Compatibility for existing keys)
        if ($decrypted === false) {
            return $value;
        }

        return $decrypted;
    }

    private function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->method));
        $encrypted = openssl_encrypt($data, $this->method, $this->key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function decrypt($data)
    {
        // Check if data has our format (base64 encoded string containing '::')
        // First decode base64
        $decoded = base64_decode($data);
        if (!$decoded || strpos($decoded, '::') === false) {
            return false; // Not our encrypted format
        }

        list($encrypted_data, $iv) = explode('::', $decoded, 2);
        return openssl_decrypt($encrypted_data, $this->method, $this->key, 0, $iv);
    }
}
