<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Car extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function boot() {

        parent::boot();

        static::updating(function (Car $car) {

            if ($car->price && $car->isDirty('price')) {

                $oldPrice = $car->getOriginal('price');

                CarPrice::create([
                    'car_id' => $car->id,
                    'price' => $car->price,
                    'old_price' => $oldPrice,
                    'diff' => $car->price - $oldPrice,
                    'updated_at' => $car->provider_updated_at,
                    'created_at' => now()
                ]);
            }

        });
    }

    public static function disable(string $provider): void
    {
        Car::query()
            ->where('provider', $provider)
            ->where('active', true)
            ->update([
                'active' => false
            ]);
    }

    public static function search(Request $request): array
    {
        $query = self::query();

        $query->select(
            'brand',
            'model',
            'version_short as version',
            'year_fabrication',
            'price',
            'odometer',
        );

        // Para carros da mesma versão/ano manter apenas o de menor odometro
        $query->addSelect(
            DB::raw('rank() over(partition by brand, model, version_short, year_fabrication, odometer order by price asc)')
        );

        $query->whereNotNull('odometer');

        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min * 1000);
        }

        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max * 1000);
        }

        if ($request->year_min) {
            $query->where('year_fabrication', '>=', $request->year_min);
        }

        if ($request->year_max) {
            $query->where('year_fabrication', '<=', $request->year_max);
        }

        if ($request->odometer_min) {
            $query->where('odometer', '>=', $request->odometer_min * 1000);
        }

        if ($request->odometer_max) {
            $query->where('odometer', '<=', $request->odometer_max * 1000);
        }

        if ($request->state) {
            $query->where('state', $request->state);
        }

        $query->groupBy(
            'brand',
            'model',
            'version_short',
            'year_fabrication',
            'price',
            'odometer',
        );

        $innerSql = vsprintf(
            str_replace('?', '%s', $query->toSql()),
            array_map(function ($binding) {
                $binding = addslashes($binding);
                return is_numeric($binding) ? $binding : "'{$binding}'";
            }, $query->getBindings())
        );

        $sql = "
        select *
        from (
            select
                *,
                rank() over(partition by brand, model, version, price, odometer order by year_fabrication desc) as rank2
            from (
                $innerSql
            ) q
        ) q2
        where q2.rank2 = 1";

        $data = DB::select($sql);

        return $data;
    }
}