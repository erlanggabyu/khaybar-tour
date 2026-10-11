<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadCicilanRequest;
use App\Models\Cicilan;
use App\Models\Pendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CicilanController extends Controller
{
    public function upload(Cicilan $cicilan): View
    {
        $this->authorizeCicilan($cicilan);
        abort_unless($cicilan->canBeUploaded() && $cicilan->isAvailableForCustomer(), 422, 'Cicilan ini belum dapat diupload.');

        return view('cicilan.upload', compact('cicilan'));
    }

    public function store(UploadCicilanRequest $request, Cicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($cicilan);
        abort_unless($cicilan->canBeUploaded() && $cicilan->isAvailableForCustomer(), 422, 'Cicilan ini belum dapat diupload.');

        $file = $request->file('bukti_file');
        $newPath = $file->storeAs('cicilan', Str::uuid().'.'.$file->extension(), 'local');
        if (! $newPath) {
            throw new RuntimeException('Bukti cicilan gagal disimpan.');
        }

        try {
            DB::transaction(function () use ($file, $newPath, $cicilan): void {
                $locked = Cicilan::lockForUpdate()->findOrFail($cicilan->id);
                abort_unless($locked->canBeUploaded() && $locked->isAvailableForCustomer(), 422, 'Cicilan ini belum dapat diupload.');

                $oldPath = $locked->bukti_file;
                $locked->update([
                    'bukti_file' => $newPath,
                    'nama_file' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'ukuran_file' => $file->getSize(),
                    'status' => Cicilan::STATUS_MENUNGGU,
                    'catatan_admin' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'paid_at' => null,
                ]);
                $locked->pendaftaran()->update(['status_pendaftaran' => Pendaftaran::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN]);

                if ($oldPath) {
                    Storage::disk('local')->delete($oldPath);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPath);
            throw $exception;
        }

        return redirect()->route('pendaftaran.pembayaran.index', $cicilan->pendaftaran)->with('success', 'Bukti cicilan berhasil diupload dan menunggu verifikasi admin.');
    }

    public function download(Cicilan $cicilan): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $cicilan->pendaftaran->user_id === $user->id, 403);
        abort_unless($cicilan->bukti_file && Storage::disk('local')->exists($cicilan->bukti_file), 404);

        return Storage::disk('local')->download($cicilan->bukti_file, $cicilan->nama_file);
    }

    private function authorizeCicilan(Cicilan $cicilan): void
    {
        abort_unless($cicilan->pendaftaran->user_id === Auth::id(), 403);
    }
}
