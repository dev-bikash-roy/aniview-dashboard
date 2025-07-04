/* global avdData, wp */
const { createElement: h, render, useState, useEffect, useRef } = wp.element;

const KPI = ({ label, value }) =>
    h('div', { className: 'avd-card' }, [
        h('h4', null, label),
        h('span', null, value)
    ]);

const App = () => {
    const [kpi, setKpi] = useState(null);
    const [series, setSeries] = useState(null);
    const canvasRef = useRef(null);
    const chartRef = useRef(null);

    useEffect(() => {
        fetch(`${avdData.root}kpi`, {
            headers: { 'X-WP-Nonce': avdData.nonce }
        })
            .then(r => r.json())
            .then(setKpi);

        fetch(`${avdData.root}timeseries?metric=revenue&from=2025-07-02&to=2025-07-02&gran=hour`, {
            headers: { 'X-WP-Nonce': avdData.nonce }
        })
            .then(r => r.json())
            .then(setSeries);
    }, []);

    useEffect(() => {
        if (!series || !canvasRef.current) return;
        if (chartRef.current) chartRef.current.destroy();
        chartRef.current = new Chart(canvasRef.current.getContext('2d'), {
            type: 'bar',
            data: {
                labels: series.map(p => p.label),
                datasets: [{ label: 'Revenue', data: series.map(p => p.value) }]
            },
            options: { responsive: true }
        });
    }, [series]);

    if (!kpi) return h('p', null, 'Loading KPIs…');

    return h('div', null, [
        h('div', { className: 'avd-kpis' }, [
            h(KPI, { label: 'Inventory',        value: kpi.inventory }),
            h(KPI, { label: 'Impression',       value: kpi.impression }),
            h(KPI, { label: 'Revenue',          value: `$${kpi.revenue}` }),
            h(KPI, { label: 'CPM',              value: `$${kpi.cpm}` }),
            h(KPI, { label: 'CTR',              value: `${kpi.ctr}%` }),
            h(KPI, { label: 'Completion Rate',  value: `${kpi.completion_rate}%` }),
        ]),
        h('canvas', { id: 'avd-rev-chart', ref: canvasRef })
    ]);
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('avd-app');
    if (root) {
        render(h(App), root);
    }
});
