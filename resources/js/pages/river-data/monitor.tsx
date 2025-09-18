import { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
// import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertTriangle, Droplets, Gauge, Activity } from 'lucide-react';
import { Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

interface RiverData {
    id: number;
    station_id: string;
    nivel: number | null;
    vazao: number | null;
    chuva: number | null;
    data_medicao: string;
}

// interface Station {
//     id: number;
//     name: string;
//     code: string;
//     status: string;
//     last_measurement: string | null;
// }

interface MonitorProps {
    recentData: RiverData[];
    stats: {
        total_stations: number;
        latest_measurement: RiverData | null;
        max_nivel: number | null;
        max_vazao: number | null;
        total_measurements: number;
    };
}

export default function Monitor({ recentData, stats }: MonitorProps) {
    const [selectedStation, setSelectedStation] = useState<string>('all');
    const [chartData, setChartData] = useState<{
        labels: string[];
        datasets: Array<{
            label: string;
            data: (number | null)[];
            borderColor: string;
            backgroundColor: string;
            yAxisID: string;
        }>;
    } | null>(null);
    const [loading, setLoading] = useState(false);

    const stations = [
        { id: 'all', name: 'Todas as Estações' },
        { id: 'RDV001', name: 'Rio das Velhas - BH' },
        { id: 'RSF001', name: 'Rio São Francisco - Pirapora' },
        { id: 'RD001', name: 'Rio Doce - GV' },
        { id: 'RPS001', name: 'Rio Paraíba do Sul - JF' },
        { id: 'RG001', name: 'Rio Grande - Divinópolis' },
    ];

    useEffect(() => {
        fetchChartData();
    }, [selectedStation]); // eslint-disable-line react-hooks/exhaustive-deps

    const fetchChartData = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/river/chart-data?station_id=${selectedStation}&days=7`);
            const data = await response.json();
            
            const chartData = {
                labels: data.data.map((item: RiverData) => 
                    new Date(item.data_medicao).toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit',
                        hour: '2-digit'
                    })
                ),
                datasets: [
                    {
                        label: 'Nível (m)',
                        data: data.data.map((item: RiverData) => item.nivel),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        yAxisID: 'y',
                    },
                    {
                        label: 'Vazão (m³/s)',
                        data: data.data.map((item: RiverData) => item.vazao),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        yAxisID: 'y1',
                    },
                    {
                        label: 'Chuva (mm)',
                        data: data.data.map((item: RiverData) => item.chuva),
                        borderColor: 'rgb(147, 51, 234)',
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        yAxisID: 'y2',
                    },
                ],
            };

            setChartData(chartData);
        } catch (error) {
            console.error('Erro ao buscar dados:', error);
        } finally {
            setLoading(false);
        }
    };

    const chartOptions = {
        responsive: true,
        interaction: {
            mode: 'index' as const,
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Data/Hora',
                },
            },
            y: {
                type: 'linear' as const,
                display: true,
                position: 'left' as const,
                title: {
                    display: true,
                    text: 'Nível (m)',
                },
            },
            y1: {
                type: 'linear' as const,
                display: true,
                position: 'right' as const,
                title: {
                    display: true,
                    text: 'Vazão (m³/s)',
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
            y2: {
                type: 'linear' as const,
                display: true,
                position: 'right' as const,
                title: {
                    display: true,
                    text: 'Chuva (mm)',
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
        },
    };

    // const getStatusColor = (status: string) => {
    //     switch (status) {
    //         case 'active': return 'bg-green-500';
    //         case 'inactive': return 'bg-red-500';
    //         case 'maintenance': return 'bg-yellow-500';
    //         default: return 'bg-gray-500';
    //     }
    // };

    const getLevelStatus = (nivel: number | null) => {
        if (nivel === null) return { status: 'unknown', color: 'bg-gray-500', text: 'Sem dados' };
        if (nivel > 5.0) return { status: 'critical', color: 'bg-red-500', text: 'Crítico' };
        if (nivel > 3.0) return { status: 'alert', color: 'bg-yellow-500', text: 'Alerta' };
        return { status: 'normal', color: 'bg-green-500', text: 'Normal' };
    };

    return (
        <AppLayout>
            <Head title="Monitoramento do Rio" />
            
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Monitoramento do Rio</h1>
                        <p className="text-muted-foreground">
                            Acompanhe em tempo real os dados das estações hidrológicas
                        </p>
                    </div>
                    <Button onClick={fetchChartData} disabled={loading}>
                        <Activity className="mr-2 h-4 w-4" />
                        Atualizar
                    </Button>
                </div>

                {/* Estatísticas */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Estações</CardTitle>
                            <Gauge className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_stations}</div>
                            <p className="text-xs text-muted-foreground">
                                Ativas e em manutenção
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Medições</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_measurements}</div>
                            <p className="text-xs text-muted-foreground">
                                Registros no sistema
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Nível Máximo</CardTitle>
                            <Droplets className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.max_nivel ? `${stats.max_nivel}m` : 'N/A'}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Altura máxima registrada
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Vazão Máxima</CardTitle>
                            <Gauge className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.max_vazao ? `${stats.max_vazao}m³/s` : 'N/A'}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Fluxo máximo registrado
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros e Gráficos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Análise Temporal</CardTitle>
                        <CardDescription>
                            Selecione uma estação para visualizar os dados detalhados
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4">
                            <Select value={selectedStation} onValueChange={setSelectedStation}>
                                <SelectTrigger className="w-[300px]">
                                    <SelectValue placeholder="Selecione uma estação" />
                                </SelectTrigger>
                                <SelectContent>
                                    {stations.map((station) => (
                                        <SelectItem key={station.id} value={station.id}>
                                            {station.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {loading ? (
                            <div className="flex h-64 items-center justify-center">
                                <div className="text-muted-foreground">Carregando dados...</div>
                            </div>
                        ) : chartData ? (
                            <div className="h-96">
                                <Line data={chartData} options={chartOptions} />
                            </div>
                        ) : (
                            <div className="flex h-64 items-center justify-center">
                                <div className="text-muted-foreground">Nenhum dado disponível</div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Dados Recentes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Dados Recentes</CardTitle>
                        <CardDescription>
                            Últimas medições das últimas 24 horas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentData.length > 0 ? (
                                recentData.map((data) => {
                                    const levelStatus = getLevelStatus(data.nivel);
                                    return (
                                        <div key={data.id} className="flex items-center justify-between rounded-lg border p-4">
                                            <div className="flex items-center space-x-4">
                                                <div className={`h-3 w-3 rounded-full ${levelStatus.color}`} />
                                                <div>
                                                    <p className="font-medium">Estação {data.station_id}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {new Date(data.data_medicao).toLocaleString('pt-BR')}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-4">
                                                <div className="text-right">
                                                    <p className="text-sm font-medium">Nível</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {data.nivel ? `${data.nivel}m` : 'N/A'}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-sm font-medium">Vazão</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {data.vazao ? `${data.vazao}m³/s` : 'N/A'}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-sm font-medium">Chuva</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {data.chuva ? `${data.chuva}mm` : 'N/A'}
                                                    </p>
                                                </div>
                                                <Badge variant={levelStatus.status === 'critical' ? 'destructive' : 'secondary'}>
                                                    {levelStatus.text}
                                                </Badge>
                                            </div>
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    Nenhum dado recente disponível
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Alertas */}
                {recentData.some(data => data.nivel && data.nivel > 3.0) && (
                    <Alert>
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>
                            <strong>Alerta:</strong> Algumas estações estão registrando níveis elevados. 
                            Monitore de perto as condições do rio.
                        </AlertDescription>
                    </Alert>
                )}
            </div>
        </AppLayout>
    );
}
