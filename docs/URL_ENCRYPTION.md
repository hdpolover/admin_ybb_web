# URL Encryption/Decryption System Documentation

## Overview

This document describes the URL encryption and decryption system used in the YBB platform for securing referral links and other sensitive data shared via URLs.

## Features

- URL-safe encryption of data for sharing in query parameters
- Support for both string and array data encryption
- Robust error handling and logging
- Security checks for data length and format
- Protection against tampering and data manipulation

## Functions

### `url_encrypt($data, $secret_key = 'ybb_program', $secret_iv = 'ybb_iv', $max_length = 4096)`

Encrypts data to make it safe for inclusion in URL query parameters.

#### Parameters:

- `$data`: String or array to encrypt
- `$secret_key`: Secret key for encryption (default: 'ybb_program')
- `$secret_iv`: Initialization vector for encryption (default: 'ybb_iv')
- `$max_length`: Maximum allowed length for input data (default: 4096)

#### Returns:

- String: URL-safe encrypted string
- False: If encryption fails

#### Example:

```php
// Encrypt a simple string
$encrypted = url_encrypt('YBB-REF-2025-05');

// Encrypt an array
$encrypted = url_encrypt(['ref_code' => 'YBB-REF-2025-05', 'source' => 'email']);

// Generate a referral link
$referralLink = "https://example.com/sign-up?q=" . urlencode($encrypted);
```

### `url_decrypt($encrypted_data, $as_array = true, $secret_key = 'ybb_program', $secret_iv = 'ybb_iv', $max_output_length = 8192)`

Decrypts data that was encrypted with `url_encrypt`.

#### Parameters:

- `$encrypted_data`: The encrypted string to decrypt
- `$as_array`: Whether to return the result as an array (default: true)
- `$secret_key`: Secret key for decryption (default: 'ybb_program')
- `$secret_iv`: Initialization vector for decryption (default: 'ybb_iv')
- `$max_output_length`: Maximum allowed length for decrypted data (default: 8192)

#### Returns:

- Array/String: Decrypted data
- False: If decryption fails

#### Example:

```php
// Decrypt to get an array (default)
$decrypted = url_decrypt($encrypted);

// Decrypt to get a string
$decrypted = url_decrypt($encrypted, false);

// In a controller receiving a query parameter
$encrypted = urldecode($_GET['q']);
$decrypted = url_decrypt($encrypted, false);
```

## Best Practices

1. **Always URL encode encrypted data when adding to URLs**:
   ```php
   $url = "https://example.com/sign-up?q=" . urlencode($encrypted);
   ```

2. **Always URL decode before decryption**:
   ```php
   $encrypted = urldecode($_GET['q']);
   $decrypted = url_decrypt($encrypted);
   ```

3. **Handle errors gracefully**:
   ```php
   $decrypted = url_decrypt($encrypted);
   if ($decrypted === false) {
       // Handle decryption error
   }
   ```

4. **Keep encryption keys secure**:
   - Do not expose the secret keys in client-side code
   - Consider moving keys to environment variables for better security
   - Rotate keys periodically for sensitive applications

5. **Validate decrypted data**:
   ```php
   $decrypted = url_decrypt($encrypted, false);
   if ($decrypted !== false && preg_match('/^YBB-REF-\d{4}-\d{2}$/', $decrypted)) {
       // Valid referral code format
   }
   ```

## Security Considerations

- The system uses AES-256-CBC encryption, which is secure for this purpose
- Base64 output is made URL-safe by replacing '+' with '-' and '/' with '_'
- Input and output length limits prevent DoS attacks
- Checks for tampering help prevent manipulation of encrypted data

## Troubleshooting

### Common Issues

1. **Decryption fails**: Check if the encrypted string was properly URL encoded/decoded
2. **Invalid format errors**: Ensure the encrypted data wasn't truncated or modified during transmission
3. **Data too long errors**: Check if you're trying to encrypt too much data for a URL parameter

### Logging

The system logs errors and debugging information to the CodeIgniter log. Check the logs for more details when issues occur.

## Testing

Use the provided test scripts to verify the encryption and decryption functions:

1. `test_url_encryption.php`: Basic encryption/decryption tests
2. `test_ambassador_referral.php`: Tests for the ambassador referral use case
3. `test_encryption_security.php`: Security-focused tests

## Example Implementation in Controllers

See `app/Controllers/Api/AmbassadorsApiController.php` for examples of using these functions in a controller context:

- `generateLink()`: Creates encrypted referral links
- `checkEncryptedQuery()`: Validates and decrypts referral codes from URLs
