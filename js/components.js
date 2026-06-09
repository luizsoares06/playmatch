const API = 'http://localhost/playmatch/api';

const PM = {
  usuario() {
    return JSON.parse(sessionStorage.getItem('usuario') || '{}');
  },

  checarLogin() {
    if (!sessionStorage.getItem('usuario')) {
      window.location.href = '/playmatch/index.html';
    }
  },

  navbar(ativa) {
    const tabs = [
      { id: 'login',      label: 'Login',      icon: '🔐', href: '/playmatch/index.html',            tag: '1' },
      { id: 'dashboard',  label: 'Dashboard',  icon: '📊', href: '/playmatch/dashboard.html',         tag: '2' },
      { id: 'listagem',   label: 'Listagem',   icon: '📋', href: '/playmatch/listagem.html',           tag: '3' },
      { id: 'formulario', label: 'Formulário', icon: '📝', href: '/playmatch/formulario.html',         tag: '4' },
      { id: 'chave',      label: 'Chave',      icon: '🏆', href: '/playmatch/chave.html',              tag: '5' },
      { id: 'relatorio',  label: 'Relatório',  icon: '📄', href: '/playmatch/relatorio.html',          tag: '6' },
    ];
    return `
      <nav class="navbar">
        <div class="navbar-brand">Play<span>Match</span></div>
        ${tabs.map(t => `
          <a href="${t.href}" class="nav-tab ${ativa === t.id ? 'active' : ''}">
            ${t.icon} ${t.label} <span class="tag">TELA ${t.tag}</span>
          </a>`).join('')}
      </nav>`;
  },

  breadcrumb(num, texto) {
    return `
      <div class="navbar-breadcrumb">
        <span class="bc-tag">TELA ${num}</span> ${texto}
      </div>`;
  },

  sidebar(ativa) {
    const u = this.usuario();
    const iniciais = u.nome ? u.nome.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase() : 'PM';
    const itens = [
      { id: 'dashboard',  label: 'Dashboard',    href: '/playmatch/dashboard.html',  icon: '📊' },
      { id: 'listagem',   label: 'Meus Torneios', href: '/playmatch/listagem.html',   icon: '🏆' },
      { id: 'chave',      label: 'Chaves',        href: '/playmatch/chave.html',      icon: '⚔️' },
      { id: 'relatorio',  label: 'Relatórios',    href: '/playmatch/relatorio.html',  icon: '📄' },
    ];
    return `
      <aside class="sidebar">
        <div class="sidebar-user">
          <div class="sidebar-avatar">${iniciais}</div>
          <div>
            <div class="sidebar-name">${u.nome || 'Usuário'}</div>
            <div class="sidebar-role">${u.perfil || ''}</div>
          </div>
        </div>
        ${itens.map(i => `
          <a href="${i.href}" class="sidebar-nav-item ${ativa === i.id ? 'active' : ''}">
            ${i.icon} ${i.label}
          </a>`).join('')}
      </aside>`;
  },

  badgeStatus(status) {
    const map = {
      'em-andamento': '<span class="badge badge-andamento">Em Andamento</span>',
      'aberto':       '<span class="badge badge-aberto">Inscrições Abertas</span>',
      'encerrado':    '<span class="badge badge-encerrado">Encerrado</span>',
    };
    return map[status] || `<span class="badge">${status}</span>`;
  },

  gameColor(modalidade) {
    const map = {
      'Valorant': '#e8490f', 'CS2': '#4ade80',
      'League of Legends': '#c084fc', 'Futsal': '#38bdf8', 'Basquete': '#fb923c',
    };
    return map[modalidade] || '#8fa3be';
  },

  fmtData(str) {
    if (!str) return '—';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
  }
};