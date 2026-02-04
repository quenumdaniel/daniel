const api = (action, payload) =>
  fetch(`api.php?action=${action}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: payload ? JSON.stringify(payload) : null,
  }).then((res) => res.json());

const formatCurrency = (value) =>
  new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    maximumFractionDigits: 0,
  }).format(Number(value || 0));

const serviceForm = document.getElementById('service-form');
const transactionForm = document.getElementById('transaction-form');
const serviceSelect = document.getElementById('service-select');
const servicesTable = document.getElementById('services-table');
const monthsTable = document.getElementById('months-table');
const trendList = document.getElementById('trend-list');
const recentList = document.getElementById('recent-list');
const currentBalance = document.getElementById('current-balance');
const monthTrend = document.getElementById('month-trend');

const renderServices = (services) => {
  servicesTable.querySelectorAll('.table-row:not(.table-header)')
    .forEach((row) => row.remove());

  serviceSelect.innerHTML = '<option value="">-- Aucun --</option>';

  services.forEach((service) => {
    const row = document.createElement('div');
    row.className = 'table-row';
    const income = Number(service.total_income || 0);
    const expense = Number(service.total_expense || 0);
    const net = income - expense;
    row.innerHTML = `
      <span>${service.service_date}</span>
      <span>${service.theme}</span>
      <span class="positive">${formatCurrency(income)}</span>
      <span class="negative">${formatCurrency(expense)}</span>
      <span class="${net >= 0 ? 'positive' : 'negative'}">${formatCurrency(net)}</span>
    `;
    servicesTable.appendChild(row);

    const option = document.createElement('option');
    option.value = service.id;
    option.textContent = `${service.service_date} - ${service.theme}`;
    serviceSelect.appendChild(option);
  });
};

const renderMonths = (months) => {
  monthsTable.querySelectorAll('.table-row:not(.table-header)')
    .forEach((row) => row.remove());

  months.forEach((month) => {
    const row = document.createElement('div');
    const net = Number(month.net_total || 0);
    row.className = 'table-row';
    row.innerHTML = `
      <span>${month.month}</span>
      <span class="positive">${formatCurrency(month.total_income)}</span>
      <span class="negative">${formatCurrency(month.total_expense)}</span>
      <span class="${net >= 0 ? 'positive' : 'negative'}">${formatCurrency(net)}</span>
    `;
    monthsTable.appendChild(row);
  });

  if (months.length >= 2) {
    const current = Number(months[months.length - 1].net_total || 0);
    const previous = Number(months[months.length - 2].net_total || 0);
    const diff = current - previous;
    const direction = diff >= 0 ? '↗︎' : '↘︎';
    monthTrend.textContent = `${direction} ${formatCurrency(diff)}`;
  } else if (months.length === 1) {
    monthTrend.textContent = `↗︎ ${formatCurrency(months[0].net_total)}`;
  } else {
    monthTrend.textContent = '--';
  }
};

const renderRecent = (transactions) => {
  recentList.innerHTML = '';
  transactions.forEach((txn) => {
    const item = document.createElement('li');
    item.className = txn.type === 'income' ? 'positive' : 'negative';
    item.innerHTML = `
      <span>${txn.txn_date}</span>
      <strong>${txn.category}</strong>
      <span>${formatCurrency(txn.amount)}</span>
      <em>${txn.description || ''}</em>
    `;
    recentList.appendChild(item);
  });
};

const renderTrend = (months) => {
  trendList.innerHTML = '';
  months.slice(-6).forEach((month) => {
    const item = document.createElement('li');
    const net = Number(month.net_total || 0);
    item.className = net >= 0 ? 'positive' : 'negative';
    item.innerHTML = `
      <span>${month.month}</span>
      <span>${net >= 0 ? 'Progression' : 'Régression'}</span>
      <strong>${formatCurrency(net)}</strong>
    `;
    trendList.appendChild(item);
  });
};

const refresh = () =>
  fetch('api.php?action=bootstrap')
    .then((res) => res.json())
    .then((data) => {
      renderServices(data.services);
      renderMonths(data.months);
      renderRecent(data.recent);
      renderTrend(data.months);
      currentBalance.textContent = formatCurrency(data.balance);
    });

serviceForm.addEventListener('submit', (event) => {
  event.preventDefault();
  const formData = new FormData(serviceForm);
  const payload = Object.fromEntries(formData.entries());
  api('add_service', payload).then(() => {
    serviceForm.reset();
    refresh();
  });
});

transactionForm.addEventListener('submit', (event) => {
  event.preventDefault();
  const formData = new FormData(transactionForm);
  const payload = Object.fromEntries(formData.entries());
  api('add_transaction', payload).then(() => {
    transactionForm.reset();
    refresh();
  });
});

['refresh-services', 'refresh-months'].forEach((id) => {
  const button = document.getElementById(id);
  if (button) {
    button.addEventListener('click', refresh);
  }
});

refresh();
