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
        $request->validate(['file' => 'required|file',
        ]);

        if ($request->encryptionType == 'AES') {
            return $this->aesEncrypt($request);
        } else if ($request->encryptionType == 'RSA') {
            return $this->rsaEncrypt($request);
        } else if ($request->encryptionType == 'Combined') {
            return $this->combinedEncrypt($request);
        }

        // Return the file upload metadata as response
        // return response()->json($fileUpload, 201);
    }

    public function aesEncrypt(Request $request)
    {
        // Generate AES Key (AES-256)
        $aes = new AES('cbc');
        $aesKey = random_bytes(32); // Panjang key untuk AES-256 adalah 32 byte
        $aes->setKey($aesKey);

        // Generate IV (Initialization Vector)
        $iv = random_bytes(16); // AES block size adalah 16 byte
        $aes->setIV($iv);

        // Baca isi file
        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());

        // Enkripsi isi file menggunakan AES
        $encryptedContent = $aes->encrypt($fileContent);

        // Buat nama file unik untuk menyimpan file terenkripsi
        $encryptedPath = 'uploads/' . uniqid() . '.enc';

        // Gabungkan IV dengan konten terenkripsi (IV disimpan untuk dekripsi nanti)
        $storageContent = $iv . $encryptedContent;

        // Simpan file terenkripsi di storage
        Storage::put($encryptedPath, $storageContent);

        // Simpan metadata file ke database menggunakan model FileUploadsModel
        $fileUpload = FileUploadsModel::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $encryptedPath,
            'aes_key' => base64_encode($aesKey), // Simpan AES key dalam format base64
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->user()->id,
            'mime_type' => $file->getClientMimeType(),
            'enc_type' => 'AES', // Tambahkan default jika tidak diisi
        ]);

        // Kembalikan respons JSON dengan informasi file
        return response()->json([
            'message' => 'File berhasil dienkripsi!',
            'file_name' => $file->getClientOriginalName(),
            'encrypted_path' => $encryptedPath,
            'aes_key' => base64_encode($aesKey), // Berikan key dalam base64 untuk referensi
        ], 201);
    }

    public function rsaEncrypt(Request $request)
    {
        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());

        // Load public key dari file atau string
        $publicKey = file_get_contents(storage_path('app/keys/public_key.pem')); // Ganti dengan path public key Anda
        $rsa = RSA::loadPublicKey($publicKey);

        // Enkripsi konten file menggunakan public key RSA
        $encryptedContent = $rsa->encrypt($fileContent);

        // Buat nama file terenkripsi
        $encryptedPath = 'uploads/' . uniqid() . '.rsa.enc';

        // Simpan konten terenkripsi di storage
        Storage::put($encryptedPath, $encryptedContent);

        // Simpan metadata file ke database
        $fileUpload = FileUploadsModel::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $encryptedPath,
            'public_key' => base64_encode($publicKey), // Simpan public key dalam base64
            'aes_key' => '', // Tambahkan default jika tidak diisi
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->user()->id,
            'mime_type' => $file->getClientMimeType(),
            'enc_type' => 'RSA',
        ]);

        return response()->json([
            'message' => 'File berhasil dienkripsi dengan RSA!',
            'file_name' => $file->getClientOriginalName(),
            'encrypted_path' => $encryptedPath,
            'public_key' => base64_encode($publicKey), // Simpan public key dalam base64 untuk referensi
        ], 201);
    }

    public function combinedEncrypt($request)
    {
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
            'enc_type' => 'Combined',
        ]);

        return response()->json($fileUpload, 201);
    }

    public function download($id)
    {
        $file = FileUploadsModel::findOrFail($id);
        if ($file->enc_type == 'AES') {
            return $this->downloadAesEncrypted($file);
        } else if ($file->enc_type == 'RSA') {
            return $this->downloadRsaEncrypted($file);
        } else if ($file->enc_type == 'Combined') {
            return $this->downloadCombinedEncrypted($file);
        }
    }

    public function downloadAesEncrypted($file)
    {
        // Cari file terenkripsi di storage
        if (!Storage::exists($file->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        // Ambil konten file terenkripsi
        $encryptedContent = Storage::get($file->file_path);

        // Ekstrak IV dari 16 byte pertama file
        $iv = substr($encryptedContent, 0, 16);

        // Ambil sisa konten sebagai data terenkripsi
        $encryptedFileContent = substr($encryptedContent, 16);

        // Decode AES key dari base64
        $aesKey = base64_decode($file->aes_key);

        // Inisialisasi AES
        $aes = new AES('cbc');
        $aes->setKey($aesKey);
        $aes->setIV($iv);

        // Dekripsi isi file
        $decryptedContent = $aes->decrypt($encryptedFileContent);

        // Kembalikan file hasil dekripsi ke pengguna
        return response($decryptedContent)
            ->header('Content-Type', $file->mime_type ?? 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
    }

    public function downloadRsaEncrypted($file)
    {
        // Cari file terenkripsi di storage
        if (!Storage::exists($file->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        // Ambil konten file terenkripsi
        $encryptedContent = Storage::get($file->file_path);

        // Load private key dari file atau string
        $privateKey = file_get_contents(storage_path('app/keys/private_key.pem')); // Ganti dengan path private key Anda
        $rsa = RSA::loadPrivateKey($privateKey);

        // Dekripsi konten file menggunakan private key RSA
        $decryptedContent = $rsa->decrypt($encryptedContent);

        // Kembalikan file hasil dekripsi ke pengguna
        return response($decryptedContent)
            ->header('Content-Type', $file->mime_type ?? 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
    }


    public function downloadCombinedEncrypted($file)
    {
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
