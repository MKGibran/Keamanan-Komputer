<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;
use App\Models\FileUpload;
use App\Models\FileUploadsModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        // Generate AES Key (AES-256)
        $aes = new AES('cbc');
        $aesKey = random_bytes(32); // AES-256
        $aes->setKey($aesKey);

        // Generate IV (Initialization Vector)
        $iv = random_bytes(16); // AES block size is 16 bytes
        $aes->setIV($iv);

        // Read File Content
        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());

        // Encrypt the file content using AES
        $encryptedContent = $aes->encrypt($fileContent);

        // Create a unique file name for the encrypted file
        $encryptedPath = 'uploads/' . uniqid() . '.enc';

        // Store the encrypted file content with the IV prepended (so we can use it later for decryption)
        $storageContent = $iv . $encryptedContent; // Prepend IV to encrypted content
        Storage::put($encryptedPath, $storageContent);

        // Encrypt AES key using RSA public key
        $publicKey = file_get_contents(storage_path('app/keys/public_key.pem'));
        $rsa = RSA::loadPublicKey($publicKey);
        $encryptedKey = $rsa->encrypt($aesKey);

        // Save the file metadata in the database
        $fileUpload = FileUploadsModel::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $encryptedPath,
            'aes_key' => base64_encode($encryptedKey), // Save the encrypted AES key in base64
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->user()->id,
            'mime_type' => $file->getClientMimeType(),
        ]);

        // Return the file upload metadata as response
        return response()->json($fileUpload, 201);
    }

    public function download($id)
    {
        $file = FileUploadsModel::findOrFail($id);

        // Dekripsi AES Key
        $privateKey = file_get_contents(storage_path('app/keys/private_key.pem'));
        $rsa = RSA::loadPrivateKey($privateKey);
        $aesKey = $rsa->decrypt(base64_decode($file->aes_key));

        // Dekripsi File
        $aes = new AES('cbc');
        $aes->setKey($aesKey);
        $encryptedContent = Storage::get($file->file_path);
        $aes->setIV(substr($encryptedContent, 0, 16)); // Gunakan IV pertama
        $decryptedContent = $aes->decrypt(substr($encryptedContent, 16));

        // Kembalikan File ke User
        return response($decryptedContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
    }

    // public function download($id)
    // {
    //     $file = FileUploadsModel::findOrFail($id);

    //     // Path ke file yang terenkripsi
    //     $encryptedFilePath = storage_path('app/public/' . $file->file_path);

    //     // Dekripsi file (misal AES)
    //     $decryptedData = $this->decryptFile($encryptedFilePath);

    //     // Simpan data yang didekripsi sementara ke file sementara
    //     $tempFilePath = storage_path('app/public/temp_decrypted_' . $file->file_name);
    //     file_put_contents($tempFilePath, $decryptedData);

    //     // Return file untuk di-download
    //     return response()->download($tempFilePath, $file->file_name)->deleteFileAfterSend(true);
    // }

    // public function decryptFile($filePath)
    // {
    //     $key = config('app.key'); // Atau kunci AES yang sesuai
    //     $cipher = 'aes-256-cbc'; // Algoritma AES yang digunakan

    //     // Ambil isi file yang terenkripsi
    //     $data = file_get_contents($filePath);

    //     // Dekripsi
    //     $iv = substr($data, 0, 16); // Ambil IV dari data terenkripsi (jika disertakan)
    //     $encryptedData = substr($data, 16);

    //     $decrypted = openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
    //     return $decrypted;
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $file = FileUploadsModel::where('id', $id)->firstOrFail();

        try {
            // Hapus file fisik dari storage (jika ada)
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }

            // Hapus data dari database
            $file->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('dashboard')->with('success', 'File deleted successfully.');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->route('dashboard')->with('error', 'Failed to delete the file. Please try again.');
        }
    }
}
