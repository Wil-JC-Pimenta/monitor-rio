import { useEffect, useState } from 'react';

interface RiverRow {
    id: number;
    station_id: string;
    nivel: number | null;
    vazao: number | null;
    chuva: number | null;
    data_medicao: string | null;
}

export default function RiverTable() {
    const [data, setData] = useState<RiverRow[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
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

    if (loading) {
        return <p>Carregando dados do rio...</p>;
    }

    return (
        <div>
            <h2>Monitoramento do Rio Piracicaba</h2>
            <table
                border={1} // <-- aqui agora é número
                cellPadding={5}
                style={{ borderCollapse: 'collapse', width: '100%' }}
            >
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
