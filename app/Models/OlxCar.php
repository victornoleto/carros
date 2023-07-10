<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OlxCar extends Model
{
    use HasFactory;

    protected $table = 'olx_cars';

    protected $guarded = [];

    protected $casts = [
        'olx_updated_at' => 'datetime'
    ];

    public static function search(Request $request): array
    {
        $query = self::query();

        $query->select(
            'brand',
            'model',
            'version',
            'year',
            'price',
            'odometer',
        );

        // Para carros da mesma versão/ano manter apenas o de menor odometro
        $query->addSelect(
            DB::raw('rank() over(partition by brand, model, version, year, odometer order by price asc)')
        );

        $query->whereNotNull('odometer');

        $query->where('price', '>', 0);

        //$query->where('active', true); TODO corrigir

        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min * 1000);
        }

        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max * 1000);
        }

        if ($request->year_min) {
            $query->where('year', '>=', $request->year_min);
        }

        if ($request->year_max) {
            $query->where('year', '<=', $request->year_max);
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
            'version',
            'year',
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
                rank() over(partition by brand, model, version, price, odometer order by year desc) as rank2
            from (
                $innerSql
            ) q
        ) q2
        where q2.rank2 = 1";

        $data = DB::select($sql);

        return $data;
    }
}