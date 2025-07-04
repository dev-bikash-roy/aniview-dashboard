/* global avdData, wp, Chart */
const { createElement: h, render, useState, useEffect, useRef } = wp.element;

const KPI = ({ label, value }) =>
    h('div', { className: 'avd-card' }, [
        h('h4', null, label),
        h('span', null, value)
    ]);

const ChartCanvas = ({ id }) => h('canvas', { id });

const Table = ({ data }) =>
    h('table', { className: 'avd-table' }, [
        h('thead', null,
            h('tr', null, [
                h('th', null, 'Name'),
                h('th', null, 'Impressions'),
                h('th', null, 'Revenue')
            ])
        ),
        h('tbody', null,
            data.map((row, i) =>
                h('tr', { key: i }, [
                    h('td', null, row.label),
                    h('td', null, row.impression),
                    h('td', null, `$${row.revenue}`)
                ])
            )
        )
    ]);

const App = () => {
    const today = new Date().toISOString().slice(0, 10);

    const [start, setStart]         = useState(today);
    const [end, setEnd]             = useState(today);
    const [granularity, setGran]    = useState('daily');
    const [kpi, setKpi]             = useState(null);
    const [country, setCountry]     = useState([]);
    const [os, setOs]               = useState([]);
    const [channel, setChannel]     = useState([]);

    const revRef    = useRef(null);
    const perfRef   = useRef(null);
    const countryRef= useRef(null);
    const osRef     = useRef(null);
    const channelRef= useRef(null);

    const fetchData = () => {
        const params = `?start=${start}&end=${end}&granularity=${granularity}`;
        fetch(`${avdData.root}kpi${params}`, { headers: { 'X-WP-Nonce': avdData.nonce }})
            .then(r => r.json())
            .then(setKpi);
        ['country','os','channel'].forEach(dim => {
            fetch(`${avdData.root}rank?dimension=${dim}&start=${start}&end=${end}`, { headers: { 'X-WP-Nonce': avdData.nonce }})
                .then(r => r.json())
                .then(data => {
                    if (dim === 'country') setCountry(data);
                    if (dim === 'os') setOs(data);
                    if (dim === 'channel') setChannel(data);
                });
        });
    };

    useEffect(fetchData, [start, end, granularity]);

    useEffect(() => {
        if (!kpi) return;

        const dates = kpi.series.map(s => s.date);
        const revenueData = kpi.series.map(s => s.revenue);
        const inventoryData = kpi.series.map(s => s.inventory);
        const impressionData = kpi.series.map(s => s.impression);

        new Chart(revRef.current, {
            type: 'line',
            data: { labels: dates, datasets: [{ label: 'Revenue', data: revenueData, borderColor: '#3e95cd', fill: false }] },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(perfRef.current, {
            type: 'line',
            data: { labels: dates, datasets: [
                { label: 'Inventory', data: inventoryData, borderColor: '#8e5ea2', fill: false },
                { label: 'Impression', data: impressionData, borderColor: '#3cba9f', fill: false }
            ] },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }, [kpi]);

    useEffect(() => {
        if (country.length)
            new Chart(countryRef.current, {
                type: 'bar',
                data: {
                    labels: country.map(r => r.label),
                    datasets: [
                        { label: 'Impression', data: country.map(r => r.impression), backgroundColor: '#3e95cd' },
                        { label: 'Revenue', data: country.map(r => r.revenue), backgroundColor: '#8e5ea2' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        if (os.length)
            new Chart(osRef.current, {
                type: 'bar',
                data: {
                    labels: os.map(r => r.label),
                    datasets: [
                        { label: 'Impression', data: os.map(r => r.impression), backgroundColor: '#3cba9f' },
                        { label: 'Revenue', data: os.map(r => r.revenue), backgroundColor: '#e8c3b9' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        if (channel.length)
            new Chart(channelRef.current, {
                type: 'bar',
                data: {
                    labels: channel.map(r => r.label),
                    datasets: [
                        { label: 'Impression', data: channel.map(r => r.impression), backgroundColor: '#c45850' },
                        { label: 'Revenue', data: channel.map(r => r.revenue), backgroundColor: '#3e95cd' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
    }, [country, os, channel]);

    if (!kpi) return h('p', null, 'Loading…');

    return h('div', null, [
        h('div', { className: 'avd-controls' }, [
            h('input', { type: 'date', value: start, onChange: e => setStart(e.target.value) }),
            h('input', { type: 'date', value: end, onChange: e => setEnd(e.target.value) }),
            h('select', { value: granularity, onChange: e => setGran(e.target.value) }, [
                h('option', { value: 'daily'  }, 'Daily'),
                h('option', { value: 'weekly' }, 'Weekly'),
                h('option', { value: 'monthly'}, 'Monthly')
            ])
        ]),
        h('div', { className: 'avd-kpis' }, [
            h(KPI, { label: 'Inventory',        value: kpi.inventory }),
            h(KPI, { label: 'Impression',       value: kpi.impression }),
            h(KPI, { label: 'Revenue',          value: `$${kpi.revenue}` }),
            h(KPI, { label: 'CPM',              value: `$${kpi.cpm}` }),
            h(KPI, { label: 'CTR',              value: `${kpi.ctr}%` }),
            h(KPI, { label: 'Completion Rate',  value: `${kpi.completion_rate}%` })
        ]),
        h('div', { className: 'avd-charts' }, [
            h('div', { className: 'avd-chart' }, h(ChartCanvas, { id: 'rev', ref: revRef })),
            h('div', { className: 'avd-chart' }, h(ChartCanvas, { id: 'perf', ref: perfRef })),
            h('div', { className: 'avd-chart' }, h(ChartCanvas, { id: 'country', ref: countryRef })),
            h('div', { className: 'avd-chart' }, h(ChartCanvas, { id: 'os', ref: osRef })),
            h('div', { className: 'avd-chart' }, h(ChartCanvas, { id: 'channel', ref: channelRef }))
        ]),
        h('div', { className: 'avd-tables' }, [
            h(Table, { data: country }),
            h(Table, { data: os }),
            h(Table, { data: channel })
        ])
    ]);
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('avd-app');
    if (root) {
        render(h(App), root);
    }
});
