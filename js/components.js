// ============================================================
// Este é o arquivo JavaScript compartilhado por todas as páginas
// Contém funções reutilizáveis de interface e utilitários
// Todas as páginas HTML importam este arquivo com:
// <script src="js/components.js"></script>
// ============================================================

// Constante global com a URL base da API PHP
// const = variável que não pode ser reatribuída
// Centraliza o endereço — se mudar o servidor, só altera aqui
// Todas as páginas usam esta variável nos fetch()
const API = 'http://localhost/playmatch/api';

// PM é um objeto JavaScript que agrupa todas as funções
// compartilhadas do sistema (funciona como um módulo)
// Evita ter muitas funções soltas no escopo global
// Acesso: PM.usuario(), PM.navbar('dashboard'), etc.
const PM = {

  // ── usuario() ──────────────────────────────────────────
  // Retorna os dados do usuário logado salvos no navegador
  // Chamada em várias páginas para exibir nome, perfil, etc.
  usuario() {
    // sessionStorage = armazenamento temporário do navegador
    // Os dados são apagados quando a aba/janela é fechada
    // Diferente do localStorage que persiste entre sessões

    // getItem('usuario') busca o item salvo com a chave 'usuario'
    // Retorna null se não existir nada salvo ainda
    // || '{}' usa objeto JSON vazio como fallback se for null
    // JSON.parse() converte a string JSON de volta para objeto JS
    // Ex: '{"nome":"João","perfil":"organizador"}' vira objeto JS
    return JSON.parse(sessionStorage.getItem('usuario') || '{}');
  },

  // ── checarLogin() ──────────────────────────────────────
  // Verifica se há um usuário logado no sistema
  // Se não houver, redireciona para a página de login
  // Chamada no início de CADA página protegida do sistema
  checarLogin() {
    // getItem retorna null se não existir nada salvo
    // !null é true — entra no if quando não está logado
    if (!sessionStorage.getItem('usuario')) {

      // window.location.href redireciona o navegador
      // Equivale a clicar em um link — muda a página atual
      // O usuário é enviado de volta para o login
      window.location.href = '/playmatch/index.html';
    }
  },

  // ── navbar() ───────────────────────────────────────────
  // Gera e retorna o HTML completo da barra de navegação
  // ativa = string com o id da aba atual (ex: 'dashboard')
  // Cada página passa seu próprio id para destacar a aba certa
  navbar(ativa) {

    // Array de objetos — cada objeto é uma aba da navbar
    // id:    identificador único usado para comparar com 'ativa'
    // label: texto exibido no botão
    // icon:  emoji do ícone antes do texto
    // href:  URL para onde o link leva ao clicar
    // tag:   número da tela exibido no badge pequeno
    const tabs = [
      { id: 'login',      label: 'Login',      icon: '🔐', href: '/playmatch/index.html',      tag: '1' },
      { id: 'dashboard',  label: 'Dashboard',  icon: '📊', href: '/playmatch/dashboard.html',   tag: '2' },
      { id: 'listagem',   label: 'Listagem',   icon: '📋', href: '/playmatch/listagem.html',     tag: '3' },
      { id: 'formulario', label: 'Formulário', icon: '📝', href: '/playmatch/formulario.html',   tag: '4' },
      { id: 'chave',      label: 'Chave',      icon: '🏆', href: '/playmatch/chave.html',        tag: '5' },
      { id: 'relatorio',  label: 'Relatório',  icon: '📄', href: '/playmatch/relatorio.html',    tag: '6' },
    ];

    // Template literal (backticks `) permite HTML multilinhas
    // e interpolar variáveis com ${}
    return `
      <nav class="navbar">

        <!-- Logo da marca no canto esquerdo -->
        <div class="navbar-brand">Play<span>Match</span></div>

        ${tabs.map(t => `
          <a href="${t.href}" class="nav-tab ${ativa === t.id ? 'active' : ''}">
            ${t.icon} ${t.label} <span class="tag">TELA ${t.tag}</span>
          </a>`)

          // map() transforma cada objeto do array em HTML de link
          // Para cada aba 't' gera uma tag <a> com o link

          // ativa === t.id ? 'active' : ''
          // Operador ternário: se a aba for a ativa
          // adiciona a classe CSS 'active' que a destaca
          // Caso contrário adiciona string vazia (sem classe)

          // join('') une todos os HTMLs gerados em uma string só
          // Sem join() o resultado seria um array, não uma string
          .join('')}
      </nav>`;
  },

  // ── breadcrumb() ───────────────────────────────────────
  // Gera a barra de localização abaixo da navbar
  // Mostra em qual tela o usuário está no momento
  // num   = número da tela (1 a 6)
  // texto = descrição da tela atual
  breadcrumb(num, texto) {

    // Retorna HTML da barra com o número em destaque e o texto
    // bc-tag = estilo CSS do badge laranja com o número
    return `
      <div class="navbar-breadcrumb">
        <span class="bc-tag">TELA ${num}</span> ${texto}
      </div>`;
  },

  // ── sidebar() ──────────────────────────────────────────
  // Gera e retorna o HTML do menu lateral esquerdo
  // ativa = string com id do item atual (ex: 'dashboard')
  sidebar(ativa) {

    // this referencia o próprio objeto PM
    // this.usuario() chama o método usuario() deste objeto
    // Obtém os dados do usuário logado para exibir na sidebar
    const u = this.usuario();

    // Gera as iniciais do nome para exibir no avatar colorido
    // u.nome existe? Então processa. Se não, usa 'PM' como padrão

    // u.nome.split(' ') divide o nome por espaço
    // Ex: 'João Carvalho' vira ['João', 'Carvalho']

    // .map(n => n[0]) pega a primeira letra de cada palavra
    // Ex: ['João', 'Carvalho'] vira ['J', 'C']

    // .join('') une as letras sem separador
    // Ex: ['J', 'C'] vira 'JC'

    // .slice(0, 2) garante no máximo 2 letras
    // .toUpperCase() deixa maiúsculo
    const iniciais = u.nome
      ? u.nome.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase()
      : 'PM';

    // Array com os itens do menu lateral
    // Mesma estrutura do array tabs da navbar
    const itens = [
      { id: 'dashboard', label: 'Dashboard',    href: '/playmatch/dashboard.html', icon: '📊' },
      { id: 'listagem',  label: 'Meus Torneios', href: '/playmatch/listagem.html',  icon: '🏆' },
      { id: 'chave',     label: 'Chaves',         href: '/playmatch/chave.html',     icon: '⚔️' },
      { id: 'relatorio', label: 'Relatórios',     href: '/playmatch/relatorio.html', icon: '📄' },
    ];

    return `
      <aside class="sidebar">

        <!-- Bloco do usuário logado no topo da sidebar -->
        <div class="sidebar-user">

          <!-- Avatar circular com as iniciais do usuário -->
          <div class="sidebar-avatar">${iniciais}</div>

          <div>
            <!-- u.nome || 'Usuário' -->
            <!-- Mostra o nome do usuário ou 'Usuário' se vazio -->
            <div class="sidebar-name">${u.nome || 'Usuário'}</div>

            <!-- Mostra o perfil: organizador, capitao, jogador -->
            <!-- || '' evita exibir 'undefined' se não existir -->
            <div class="sidebar-role">${u.perfil || ''}</div>
          </div>
        </div>

        <!-- Gera os links do menu com map() e join() -->
        <!-- Mesmo padrão da navbar: adiciona 'active' no item atual -->
        ${itens.map(i => `
          <a href="${i.href}" class="sidebar-nav-item ${ativa === i.id ? 'active' : ''}">
            ${i.icon} ${i.label}
          </a>`).join('')}

      </aside>`;
  },

  // ── badgeStatus() ──────────────────────────────────────
  // Converte o status do banco (texto simples) em HTML colorido
  // Cada status tem uma cor diferente definida no CSS
  // status = string vinda do banco: 'em-andamento', 'aberto', etc.
  badgeStatus(status) {

    // Objeto usado como dicionário/mapa de conversão
    // Chave = valor que vem do banco
    // Valor = HTML do badge com a classe CSS correspondente
    const map = {
      // badge-andamento = laranja (definido no style.css)
      'em-andamento': '<span class="badge badge-andamento">Em Andamento</span>',

      // badge-aberto = verde (definido no style.css)
      'aberto':       '<span class="badge badge-aberto">Inscrições Abertas</span>',

      // badge-encerrado = cinza (definido no style.css)
      'encerrado':    '<span class="badge badge-encerrado">Encerrado</span>',
    };

    // map[status] busca o HTML pelo status recebido
    // || retorna badge genérico se o status não estiver no mapa
    // Evita erros se vier um status inesperado do banco
    return map[status] || `<span class="badge">${status}</span>`;
  },

  // ── gameColor() ────────────────────────────────────────
  // Retorna a cor hexadecimal correspondente a cada modalidade
  // Usada para colorir tags e elementos visuais dos torneios
  // modalidade = string como 'Valorant', 'CS2', etc.
  gameColor(modalidade) {

    // Mapa de modalidade para cor hexadecimal
    // Cada jogo/esporte tem uma cor característica
    const map = {
      'Valorant':          '#e8490f', // laranja (cor oficial do jogo)
      'CS2':               '#4ade80', // verde
      'League of Legends': '#c084fc', // roxo
      'Futsal':            '#38bdf8', // azul claro
      'Basquete':          '#fb923c', // laranja claro
    };

    // Retorna a cor da modalidade encontrada no mapa
    // || '#8fa3be' retorna cinza padrão se não encontrar
    return map[modalidade] || '#8fa3be';
  },

  // ── fmtData() ──────────────────────────────────────────
  // Formata datas do padrão do banco para o padrão brasileiro
  // Banco salva: YYYY-MM-DD (ex: 2026-06-01)
  // Exibe como: DD/MM/YYYY (ex: 01/06/2026)
  fmtData(str) {

    // Se a string for vazia, null ou undefined retorna traço
    // Evita erros ao tentar formatar um valor vazio
    if (!str) return '—';

    // split('-') divide a string pelo traço em array
    // '2026-06-01' vira ['2026', '06', '01']

    // Destructuring: extrai os 3 valores do array de uma vez
    // const [y, m, d] = ['2026', '06', '01']
    // y = '2026' (year/ano)
    // m = '06'   (month/mês)
    // d = '01'   (day/dia)
    const [y, m, d] = str.split('-');

    // Template literal monta no formato brasileiro DD/MM/YYYY
    // Inverte a ordem: dia/mês/ano
    return `${d}/${m}/${y}`;
  }

}; // fim do objeto PM