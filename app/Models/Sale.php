<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'total',
        'invoice_number',
        'status',
        'is_quotation', // 🔹 Nuevo campo para diferenciar cotización/venta
    ];

    protected $casts = [
        'is_quotation' => 'boolean',
    ];

    /**
     * Relación: Una venta tiene muchos detalles.
     */
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Relación: Una venta pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generar un nuevo número de factura único.
     */
    public static function generateInvoiceNumber()
    {
        $branch = '001'; // puedes cambiarlo dinámicamente si tienes sucursales
        $lastSale = self::latest('id')->first();
        $nextNumber = $lastSale ? $lastSale->id + 1 : 1;
        $formattedNumber = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        return "{$branch}-{$formattedNumber}";
    }

    /**
     * Calcular el total de la venta basado en los detalles.
     */
    public function calculateTotal()
    {
        return $this->details->sum(fn ($detail) => $detail->calculateSubtotal());
    }

    /**
     * Convertir una cotización en venta (actualiza stock).
     */
    public function convertToSale()
    {
        if ($this->is_quotation) {
            DB::transaction(function () {
                $this->update(['is_quotation' => false]);

                foreach ($this->details as $detail) {
                    $detail->article->decreaseStock($detail->quantity);
                }
            });
        }
    }
}
