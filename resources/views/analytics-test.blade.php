<!DOCTYPE html>
<html>
<head>
    <title>Analytics Test</title>
</head>
<body>
    <h1>Analytics Test</h1>
    <p>Max Nivel: {{ $maxNivel ?? 'N/A' }}</p>
    <p>Min Nivel: {{ $minNivel ?? 'N/A' }}</p>
    <p>Max Vazao: {{ $maxVazao ?? 'N/A' }}</p>
    <p>Total Chuva: {{ $totalChuva ?? 'N/A' }}</p>
    <p>Stations Count: {{ count($stations ?? []) }}</p>
</body>
</html>
