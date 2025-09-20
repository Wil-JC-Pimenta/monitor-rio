import { useEffect, useState, useRef } from 'react';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

interface RiverRow {
    id: number;
    station_id: string;
    nivel: number | null;
    vazao: number | null;
    chuva: number | null;
    data_medicao: string | null;
}

export default function RiverDashboard() {
    const [data, setData] = useState<RiverRow[]>([]);
    const [loading, setLoading] = useState(true);
    const chartRef = useRef<HTMLCanvasElement | null>(null);
    const chartInstance = useRef<Chart | null>(null);

    useEffect(() => {
        // Buscar dados da API
        fetch('/api/river-data')
            .then((res) => res.json())
            .then((json) => {
                setData(json);
                setLoading(false);
            })
            .catch((err) => {
                console.error('Erro ao buscar dados da API:', err);
                setLoading(false);
            });
    }, []);

    useEffect(() => {
        if (!chartRef.current || data.length === 0) return;

        const ctx = chartRef.current.getContext('2d');
        if (!ctx) return;

        // Limpar gráfico antigo
        if (chartInstance.current) {
            chartInstance.current.destroy();
        }

        chartInstance.current = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((d) => (d.data_medicao ? new Date(d.data_medicao).toLocaleString() : '-')),
                datasets: [
                    {
                        label: 'Nível do Rio (m)',
                        data: data.map((d) => d.nivel ?? 0),
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0,0,255,0.2)',
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            },
        });
    }, [data]);

    if (loading) return <p>Carregando dados do rio...</p>;

    return (
        <div>
            <h2>Monitoramento do Rio Piracicaba</h2>

            <div style={{ height: 300, marginBottom: 20 }}>
                <canvas ref={chartRef}></canvas>
            </div>

            <table border={1} cellPadding={5} style={{ borderCollapse: 'collapse', width: '100%' }}>
                <thead>
                    <tr>
                        <th>Estação</th>
                        <th>Nível (m)</th>
                        <th>Vazão (m³/s)</th>
                        <th>Chuva (mm)</th>
                        <th>Data Medição</th>
                    </tr>
                </thead>
                <tbody>
                    {data.length === 0 && (
                        <tr>
                            <td colSpan={5}>Nenhum dado disponível.</td>
                        </tr>
                    )}
                    {data.map((row) => (
                        <tr key={row.id}>
                            <td>{row.station_id}</td>
                            <td>{row.nivel ?? '-'}</td>
                            <td>{row.vazao ?? '-'}</td>
                            <td>{row.chuva ?? '-'}</td>
                            <td>{row.data_medicao ? new Date(row.data_medicao).toLocaleString() : '-'}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
