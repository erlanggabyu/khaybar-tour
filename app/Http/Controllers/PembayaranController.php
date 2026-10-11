<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPembayaranRequest;
use App\Models\Pembayaran;
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

class PembayaranController extends Controller
{
    public function index(Pendaftaran $pendaftaran): View
    {
        $this->authorizeRegistration($pendaftaran);
        $pendaftaran->ensurePaymentBills();
        $pendaftaran->load(['paketUmroh', 'pembayarans.verifier', 'cicilans.verifier']);

        return view('pembayaran.index', compact('pendaftaran'));
    }

    public function upload(Pembayaran $pembayaran): View
    {
        $this->authorizePayment($pembayaran);
        abort_unless($pembayaran->canBeUploaded() && $pembayaran->isAvailableForCustomer(), 422, 'Bukti pembayaran ini belum dapat diupload.');

        return view('pembayaran.upload', compact('pembayaran'));
    }

    public function store(UploadPembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorizePayment($pembayaran);
        abort_unless($pembayaran->canBeUploaded() && $pembayaran->isAvailableForCustomer(), 422, 'Bukti pembayaran ini belum dapat diupload.');

        $file = $request->file('bukti_file');
        $newPath = $file->storeAs('pembayaran', Str::uuid().'.'.$file->extension(), 'local');
        if (! $newPath) {
            throw new RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        try {
            DB::transaction(function () use ($file, $newPath, $pembayaran): void {
                $locked = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);
                abort_unless($locked->canBeUploaded() && $locked->isAvailableForCustomer(), 422, 'Bukti pembayaran ini belum dapat diupload.');

                $oldPath = $locked->bukti_file;
                $locked->update([
                    'bukti_file' => $newPath,
                    'nama_file' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'ukuran_file' => $file->getSize(),
                    'status_pembayaran' => Pembayaran::STATUS_MENUNGGU,
                    'catatan_admin' => null,
                    'verified_by' => null,
                    'verified_at' => null,
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

        return redirect()->route('pendaftaran.pembayaran.index', $pembayaran->pendaftaran)->with('success', 'Bukti pembayaran berhasil diupload dan menunggu verifikasi admin.');
    }

    public function download(Pembayaran $pembayaran): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $pembayaran->pendaftaran->user_id === $user->id, 403);
        abort_unless($pembayaran->bukti_file && Storage::disk('local')->exists($pembayaran->bukti_file), 404);

        return Storage::disk('local')->download($pembayaran->bukti_file, $pembayaran->nama_file);
    }

    private function authorizeRegistration(Pendaftaran $pendaftaran): void
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);
    }

    private function authorizePayment(Pembayaran $pembayaran): void
    {
        abort_unless($pembayaran->pendaftaran->user_id === Auth::id(), 403);
    }
}
