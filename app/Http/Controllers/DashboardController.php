<?php

namespace App\Http\Controllers;

use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const ALLOWED_PERIODS = ['week', '15days', 'month', 'year'];

    public function index(Request $request): Response
    {
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $periodParam = $request->query('period');
        $period = is_string($periodParam) && in_array($periodParam, self::ALLOWED_PERIODS, true)
            ? $periodParam
            : 'week';

        [$from, $to] = $this->dateRange($period);

        $leadsByDay = $this->countByDay(
            Lead::query()->forCompany($companyId)->whereBetween('created_at', [$from, $to]),
            $from,
            $to,
            $period,
        );

        $customersByDay = $this->countByDay(
            Customer::query()->forCompany($companyId)->whereBetween('created_at', [$from, $to]),
            $from,
            $to,
            $period,
        );

        $ordersByDay = $this->countByDay(
            Order::query()->forCompany($companyId)->whereBetween('created_at', [$from, $to]),
            $from,
            $to,
            $period,
        );

        $topProducts = OrderItem::query()
            ->selectRaw('sales_order_items.product_id, SUM(sales_order_items.quantity) as total_quantity')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.order_id')
            ->where('sales_orders.company_id', $companyId)
            ->whereBetween('sales_orders.created_at', [$from, $to])
            ->groupBy('sales_order_items.product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(fn (OrderItem $item): array => [
                'name' => (string) ($item->product?->name ?? ''),
                'code' => (string) ($item->product?->code ?? ''),
                'total_quantity' => (int) $item->total_quantity,
            ])
            ->values()
            ->all();

        $soldInPeriod = OrderItem::query()
            ->selectRaw('sales_order_items.product_id, SUM(sales_order_items.quantity) as sold_quantity')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.order_id')
            ->where('sales_orders.company_id', $companyId)
            ->whereBetween('sales_orders.created_at', [$from, $to])
            ->groupBy('sales_order_items.product_id')
            ->pluck('sold_quantity', 'product_id');

        $lowStockProducts = Product::query()
            ->forCompany($companyId)
            ->where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => (string) $product->name,
                'code' => (string) ($product->code ?? ''),
                'sku' => (string) ($product->sku ?? ''),
                'minimum_stock' => (int) $product->minimum_stock,
                'sold_in_period' => (int) ($soldInPeriod[$product->id] ?? 0),
            ])
            ->values()
            ->all();

        return Inertia::render('dashboard', [
            'leads_by_day' => $leadsByDay,
            'customers_by_day' => $customersByDay,
            'orders_by_day' => $ordersByDay,
            'top_products' => $topProducts,
            'low_stock_products' => $lowStockProducts,
            'period' => $period,
        ]);
    }

    /**
     * Agrupa un query por día (o mes si period = year) y rellena días sin datos con 0.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array<int, array{label: string, count: int}>
     */
    private function countByDay(
        \Illuminate\Database\Eloquent\Builder $query,
        string $from,
        string $to,
        string $period,
    ): array {
        $groupByMonth = $period === 'year';

        if ($groupByMonth) {
            $rows = $query
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, COUNT(*) as count")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('count', 'bucket');
        } else {
            $rows = $query
                ->selectRaw('DATE(created_at) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('count', 'bucket');
        }

        return $this->fillGaps($rows, $from, $to, $groupByMonth);
    }

    /**
     * Rellena los intervalos sin datos con count = 0.
     *
     * @param  Collection<string, mixed>  $rows
     * @return array<int, array{label: string, count: int}>
     */
    private function fillGaps(Collection $rows, string $from, string $to, bool $byMonth): array
    {
        $result = [];
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);

        if ($byMonth) {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m');
                $result[] = [
                    'label' => $cursor->translatedFormat('M y'),
                    'count' => (int) ($rows[$key] ?? 0),
                ];
                $cursor->addMonth();
            }
        } else {
            foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $day) {
                $key = $day->toDateString();
                $result[] = [
                    'label' => $day->format('d/m'),
                    'count' => (int) ($rows[$key] ?? 0),
                ];
            }
        }

        return $result;
    }

    /**
     * @return array{string, string}
     */
    private function dateRange(string $period): array
    {
        $now = Carbon::now();

        $from = match ($period) {
            '15days' => $now->copy()->subDays(15)->startOfDay(),
            'month' => $now->copy()->subMonth()->startOfDay(),
            'year' => $now->copy()->subYear()->startOfDay(),
            default => $now->copy()->subWeek()->startOfDay(),
        };

        return [$from->toDateTimeString(), $now->copy()->endOfDay()->toDateTimeString()];
    }
}
