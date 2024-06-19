<?php

class EncryptionController extends Controller
{
    protected $key;
    function __construct()
    {
        $this->key = "put your key";
    }

    public function encryptImage(Request $request)
    {
        $image = $request->file('file');
        $key = $this->key;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedImage = openssl_encrypt(file_get_contents($image->getRealPath()), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $encryptedData = base64_encode($iv . $encryptedImage);
        $encryptedPath = 'temp' . uniqid() . '.enc';
        $path = Storage::put("encrypted/$encryptedPath", $encryptedData);
        return response()->download(storage_path("app/encrypted/" . $encryptedPath));
    }

    public function decryptImage(Request $request)
    {
        $key = $this->key;
        $encryptedPath = $request->file('file');
        $encryptedData = file_get_contents($encryptedPath->getRealPath());
        $encryptedData = base64_decode($encryptedData);
        $iv = substr($encryptedData, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedImage = substr($encryptedData, openssl_cipher_iv_length('aes-256-cbc'));
        $decryptedImage = openssl_decrypt($encryptedImage, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $decryptedPath = 'decrypted_' . uniqid() . $encryptedPath->getClientOriginalName();
        $path = Storage::put("decrypted/$decryptedPath", $decryptedImage);
        $downloadLink = Storage::url("decrypted/" . $decryptedPath);
        return response()->json([
            'image' => $downloadLink
        ]);
    }
}
