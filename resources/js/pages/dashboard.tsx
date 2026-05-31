import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { dashboard } from '@/routes';
import {
    AreaChart,
    Area,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    Cell,
} from 'recharts';

type Period = 'week' | '15days' | 'month' | 'year';

interface DayCount {
    label: string;
    count: number;
}

interface TopProduct {
    name: string;
    code: string;
    total_quantity: number;
}

interface LowStockProduct {
    name: string;
    code: string;
    sku: string;
    minimum_stock: number;
    sold_in_period: number;
}

interface DashboardProps {
    leads_by_day: DayCount[];
    customers_by_day: DayCount[];
    orders_by_day: DayCount[];
    top_products: TopProduct[];
    low_stock_products: LowStockProduct[];
    period: Period;
}

const PERIOD_LABELS: Record<Period, string> = {
    week: 'Semana',
    '15days': '15 días',
    month: 'Mes',
    year: 'Año',
};

const BAR_COLORS = [
    'hsl(221, 83%, 53%)',
    'hsl(221, 83%, 60%)',
    'hsl(221, 83%, 67%)',
    'hsl(221, 83%, 74%)',
    'hsl(221, 83%, 81%)',
];

const TOOLTIP_STYLE = {
    borderRadius: '6px',
    border: '1px solid hsl(var(--border))',
    background: 'hsl(var(--background))',
    fontSize: '12px',
    padding: '6px 10px',
};

function truncateLabel(label: string, maxLength = 16): string {
    return label.length > maxLength ? label.slice(0, maxLength) + '…' : label;
}

interface MetricCardProps {
    title: string;
    data: DayCount[];
    color: string;
    period: Period;
}

function MetricCard({ title, data, color, period }: MetricCardProps) {
    const total = data.reduce((sum, d) => sum + d.count, 0);
    const gradientId = `grad-${title.replace(/\s+/g, '-')}`;

    const tickInterval = period === 'year'
        ? 1
        : period === 'month'
            ? Math.floor(data.length / 5)
            : 0;

    return (
        <Card className="py-0 gap-0">
            <CardHeader className="px-4 pt-3 pb-1">
                <CardTitle className="text-xs font-medium text-muted-foreground">{title}</CardTitle>
                <p className="text-2xl font-bold leading-tight">{total}</p>
            </CardHeader>
            <CardContent className="px-2 pb-2">
                <ResponsiveContainer width="100%" height={80}>
                    <AreaChart data={data} margin={{ top: 4, right: 4, left: -28, bottom: 0 }}>
                        <defs>
                            <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor={color} stopOpacity={0.25} />
                                <stop offset="95%" stopColor={color} stopOpacity={0} />
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" className="stroke-border" vertical={false} />
                        <XAxis
                            dataKey="label"
                            tick={{ fontSize: 10 }}
                            interval={tickInterval}
                            axisLine={false}
                            tickLine={false}
                        />
                        <YAxis allowDecimals={false} tick={{ fontSize: 10 }} axisLine={false} tickLine={false} />
                        <Tooltip
                            contentStyle={TOOLTIP_STYLE}
                            formatter={(value: number) => [value, title]}
                        />
                        <Area
                            type="monotone"
                            dataKey="count"
                            stroke={color}
                            strokeWidth={2}
                            fill={`url(#${gradientId})`}
                            dot={false}
                            activeDot={{ r: 3 }}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}

interface ProductTooltipProps {
    active?: boolean;
    payload?: Array<{ payload: TopProduct }>;
}

function ProductTooltip({ active, payload }: ProductTooltipProps) {
    if (!active || !payload?.length) return null;
    const item = payload[0].payload;
    return (
        <div className="rounded-md border bg-background px-2.5 py-1.5 shadow text-xs">
            <p className="font-medium">{item.name || item.code}</p>
            {item.code && item.name && <p className="text-muted-foreground">{item.code}</p>}
            <p className="mt-0.5 font-semibold">{item.total_quantity} uds</p>
        </div>
    );
}

export default function Dashboard({
    leads_by_day,
    customers_by_day,
    orders_by_day,
    top_products,
    low_stock_products,
    period,
}: DashboardProps) {
    function changePeriod(value: string) {
        router.get(dashboard(), { period: value }, { preserveScroll: true });
    }

    const chartData = top_products.map((p) => ({
        ...p,
        label: truncateLabel(p.name || p.code),
    }));

    return (
        <>
            <Head title="Panel" />
            <div className="flex h-full flex-1 flex-col gap-3 overflow-x-auto p-3">

                {/* Header */}
                <div className="flex items-center justify-between gap-3 flex-wrap">
                    <h1 className="text-base font-semibold">Panel de control</h1>
                    <Tabs value={period} onValueChange={changePeriod}>
                        <TabsList>
                            {(Object.keys(PERIOD_LABELS) as Period[]).map((key) => (
                                <TabsTrigger key={key} value={key} className="text-xs px-3">
                                    {PERIOD_LABELS[key]}
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                </div>

                {/* KPI charts por día */}
                <div className="grid gap-3 md:grid-cols-3">
                    <MetricCard
                        title="Nuevos leads"
                        data={leads_by_day}
                        color="hsl(221, 83%, 53%)"
                        period={period}
                    />
                    <MetricCard
                        title="Nuevos clientes"
                        data={customers_by_day}
                        color="hsl(142, 71%, 45%)"
                        period={period}
                    />
                    <MetricCard
                        title="Nuevos pedidos"
                        data={orders_by_day}
                        color="hsl(38, 92%, 50%)"
                        period={period}
                    />
                </div>

                {/* Productos más vendidos + stock */}
                <div className="grid gap-3 lg:grid-cols-2">

                    <Card className="py-0 gap-0">
                        <CardHeader className="px-4 pt-3 pb-2">
                            <CardTitle className="text-sm">Productos más vendidos</CardTitle>
                        </CardHeader>
                        <CardContent className="px-2 pb-3">
                            {chartData.length === 0 ? (
                                <p className="text-xs text-muted-foreground py-6 text-center">
                                    Sin ventas en este período.
                                </p>
                            ) : (
                                <ResponsiveContainer width="100%" height={Math.min(260, Math.max(160, chartData.length * 28))}>
                                    <BarChart
                                        data={chartData}
                                        layout="vertical"
                                        margin={{ top: 2, right: 36, left: 4, bottom: 2 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" horizontal={false} className="stroke-border" />
                                        <XAxis type="number" allowDecimals={false} tick={{ fontSize: 11 }} axisLine={false} tickLine={false} />
                                        <YAxis type="category" dataKey="label" width={100} tick={{ fontSize: 11 }} axisLine={false} tickLine={false} />
                                        <Tooltip content={<ProductTooltip />} />
                                        <Bar dataKey="total_quantity" name="Unidades" radius={[0, 3, 3, 0]} barSize={14}>
                                            {chartData.map((_, i) => (
                                                <Cell key={i} fill={BAR_COLORS[i % BAR_COLORS.length]} />
                                            ))}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="py-0 gap-0">
                        <CardHeader className="px-4 pt-3 pb-2">
                            <CardTitle className="text-sm">
                                Stock mínimo — {PERIOD_LABELS[period]}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-0 pb-0">
                            {low_stock_products.length === 0 ? (
                                <p className="px-4 pb-4 text-xs text-muted-foreground">
                                    No hay productos con stock mínimo configurado.
                                </p>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow className="text-xs">
                                            <TableHead className="h-8 px-3">Producto</TableHead>
                                            <TableHead className="h-8 text-right px-3">Mín.</TableHead>
                                            <TableHead className="h-8 text-right px-3">Vendido</TableHead>
                                            <TableHead className="h-8 text-right px-3">Estado</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {low_stock_products.map((product, i) => {
                                            const isAlert = product.sold_in_period >= product.minimum_stock;
                                            return (
                                                <TableRow key={i} className="text-xs">
                                                    <TableCell className="px-3 py-1.5">
                                                        <p className="font-medium leading-none truncate max-w-[150px]" title={product.name}>
                                                            {product.name}
                                                        </p>
                                                        {product.code && (
                                                            <p className="text-muted-foreground text-[11px] mt-0.5">{product.code}</p>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right px-3 py-1.5">{product.minimum_stock}</TableCell>
                                                    <TableCell className="text-right px-3 py-1.5">{product.sold_in_period}</TableCell>
                                                    <TableCell className="text-right px-3 py-1.5">
                                                        <span className={`inline-flex items-center rounded-full px-1.5 py-0.5 text-[11px] font-medium ${isAlert ? 'bg-destructive/10 text-destructive' : 'bg-green-500/10 text-green-600'}`}>
                                                            {isAlert ? 'Revisar' : 'OK'}
                                                        </span>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>

                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Panel', href: dashboard() }],
};
