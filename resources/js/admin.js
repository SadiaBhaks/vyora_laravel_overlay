import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend);

window.vyoraRevenueChart = (canvas, data) => {
    const css = getComputedStyle(document.documentElement);
    const ink = css.getPropertyValue('--ink').trim();
    const muted = css.getPropertyValue('--muted').trim();
    const line = css.getPropertyValue('--line').trim();
    const accent = css.getPropertyValue('--accent-text').trim() || ink;

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Paid orders', data: data.real, borderColor: accent, backgroundColor: 'transparent', borderWidth: 2, tension: 0.35, pointRadius: 0, pointHoverRadius: 4 },
                { label: 'Demo-mode orders', data: data.demo, borderColor: muted, backgroundColor: 'transparent', borderWidth: 1.5, borderDash: [4, 4], tension: 0.35, pointRadius: 0, pointHoverRadius: 4 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: muted, usePointStyle: true, boxWidth: 8 } },
                tooltip: { callbacks: { label: (c) => `${c.dataset.label}: $${Number(c.parsed.y).toFixed(2)}` } },
            },
            scales: {
                x: { ticks: { color: muted, maxTicksLimit: 8 }, grid: { display: false }, border: { color: line } },
                y: { beginAtZero: true, ticks: { color: muted, callback: (v) => '$' + v }, grid: { color: line }, border: { display: false } },
            },
        },
    });
};
