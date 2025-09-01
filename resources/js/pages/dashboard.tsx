import { useState, useEffect } from 'react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Droplets, Gauge, Activity, AlertTriangle, MapPin, Clock } from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface RiverStats {
    total_stations: number;
    total_measurements: number;
    max_nivel: number | null;
    max_vazao: number | null;
    critical_alerts: number;
}

export default function Dashboard() {
    const [riverStats, setRiverStats] = useState<RiverStats | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchRiverStats();
    }, []);

    const fetchRiverStats = async () => {
        try {
            const response = await fetch('/river/chart-data?days=1');
            const data = await response.json();
            
            // Simular estatísticas baseadas nos dados
            const stats: RiverStats = {
                total_stations: 5,
                total_measurements: data.data?.length || 0,
                max_nivel: Math.max(...(data.data?.map((d: any) => d.nivel || 0) || [0])),
                max_vazao: Math.max(...(data.data?.map((d: any) => d.vazao || 0) || [0])),
                critical_alerts: data.data?.filter((d: any) => d.nivel > 3.0).length || 0,
            };
            
            setRiverStats(stats);
        } catch (error) {
            console.error('Erro ao buscar estatísticas:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                
                {/* Header do Dashboard */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Dashboard</h1>
                        <p className="text-muted-foreground">
                            Visão geral do sistema de monitoramento
                        </p>
                    </div>
                    <Link href="/river/monitor">
                        <Button>
                            <Activity className="mr-2 h-4 w-4" />
                            Ver Monitoramento
                        </Button>
                    </Link>
                </div>

                {/* Estatísticas do Sistema */}
                <div className="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Estações Ativas</CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {loading ? '...' : riverStats?.total_stations || 0}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Estações hidrológicas
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Medições</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {loading ? '...' : riverStats?.total_measurements || 0}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Registros coletados
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
                                {loading ? '...' : riverStats?.max_nivel ? `${riverStats.max_nivel.toFixed(2)}m` : 'N/A'}
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
                                {loading ? '...' : riverStats?.max_vazao ? `${riverStats.max_vazao.toFixed(1)}m³/s` : 'N/A'}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Fluxo máximo registrado
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Alertas e Status */}
                {riverStats && riverStats.critical_alerts > 0 && (
                    <Alert>
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>
                            <strong>Alerta:</strong> {riverStats.critical_alerts} estação(ões) com níveis críticos. 
                            <Link href="/river/monitor" className="ml-2 underline">
                                Ver detalhes
                            </Link>
                        </AlertDescription>
                    </Alert>
                )}

                {/* Cards de Acesso Rápido */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card className="cursor-pointer hover:shadow-md transition-shadow">
                        <Link href="/river/monitor">
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Activity className="mr-2 h-5 w-5" />
                                    Monitoramento em Tempo Real
                                </CardTitle>
                                <CardDescription>
                                    Acompanhe os dados das estações hidrológicas
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Visualize gráficos, métricas e alertas do sistema de monitoramento
                                </p>
                            </CardContent>
                        </Link>
                    </Card>

                    <Card className="cursor-pointer hover:shadow-md transition-shadow">
                        <Link href="/river/data">
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Gauge className="mr-2 h-5 w-5" />
                                    Histórico de Dados
                                </CardTitle>
                                <CardDescription>
                                    Consulte o histórico completo de medições
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Analise tendências e padrões ao longo do tempo
                                </p>
                            </CardContent>
                        </Link>
                    </Card>

                    <Card className="cursor-pointer hover:shadow-md transition-shadow">
                        <Link href="/settings">
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Clock className="mr-2 h-5 w-5" />
                                    Configurações
                                </CardTitle>
                                <CardDescription>
                                    Gerencie suas preferências e configurações
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Personalize notificações e alertas do sistema
                                </p>
                            </CardContent>
                        </Link>
                    </Card>
                </div>

                {/* Área de Gráficos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Visão Geral do Sistema</CardTitle>
                        <CardDescription>
                            Status atual das estações e principais indicadores
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="relative min-h-[400px] overflow-hidden rounded-xl">
                            {loading ? (
                                <div className="flex h-full items-center justify-center">
                                    <div className="text-muted-foreground">Carregando dados...</div>
                                </div>
                            ) : (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <h3 className="font-semibold">Status das Estações</h3>
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <span>Rio das Velhas - BH</span>
                                                <Badge variant="default" className="bg-green-500">Online</Badge>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span>Rio São Francisco - Pirapora</span>
                                                <Badge variant="default" className="bg-green-500">Online</Badge>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span>Rio Doce - GV</span>
                                                <Badge variant="default" className="bg-green-500">Online</Badge>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span>Rio Paraíba do Sul - JF</span>
                                                <Badge variant="secondary">Manutenção</Badge>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span>Rio Grande - Divinópolis</span>
                                                <Badge variant="default" className="bg-green-500">Online</Badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <h3 className="font-semibold">Últimas Atualizações</h3>
                                        <div className="space-y-2 text-sm text-muted-foreground">
                                            <div className="flex items-center">
                                                <Clock className="mr-2 h-4 w-4" />
                                                <span>Última medição: há 2 horas</span>
                                            </div>
                                            <div className="flex items-center">
                                                <Activity className="mr-2 h-4 w-4" />
                                                <span>5 estações ativas</span>
                                            </div>
                                            <div className="flex items-center">
                                                <Droplets className="mr-2 h-4 w-4" />
                                                <span>Nível médio: 2.8m</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
