/* global avdData, wp */
const { createElement: h, render, useState, useEffect } = wp.element;

const KPI = ({ label, value }) =>
    h('div', { className: 'avd-card' }, [
        h('h4', null, label),
        h('span', null, value)
    ]);

const App = () => {
    const [kpi, setKpi] = useState(null);

    useEffect(() => {
        fetch(`${avdData.root}kpi`, {
            headers: { 'X-WP-Nonce': avdData.nonce }
        })
            .then(r => r.json())
            .then(setKpi);
    }, []);

    if (!kpi) return h('p', null, 'Loading KPIs…');

    return h('div', { className: 'avd-kpis' }, [
        h(KPI, { label: 'Inventory',        value: kpi.inventory }),
        h(KPI, { label: 'Impression',       value: kpi.impression }),
        h(KPI, { label: 'Revenue',          value: `$${kpi.revenue}` }),
        h(KPI, { label: 'CPM',              value: `$${kpi.cpm}` }),
        h(KPI, { label: 'CTR',              value: `${kpi.ctr}%` }),
        h(KPI, { label: 'Completion Rate',  value: `${kpi.completion_rate}%` }),
    ]);
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('avd-app');
    if (root) {
        render(h(App), root);
    }
});
