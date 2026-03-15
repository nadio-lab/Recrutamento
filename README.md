# Emprega — Plataforma de Recrutamento Online
### PHP 8 · MySQL · Bootstrap 5 · Responsivo · Angola

---

## 📁 ESTRUTURA DE FICHEIROS

```
recrutamento/
├── index.php              ← Homepage pública (listagem de vagas)
├── vaga.php               ← Detalhe da vaga + formulário candidatura
├── registar.php           ← Registo (candidato ou empresa)
├── login.php              ← Login
├── logout.php             ← Logout
├── empresa.php            ← Perfil público da empresa (a criar)
├── empresas.php           ← Listagem pública de empresas (a criar)
├── categorias.php         ← Listagem de categorias (a criar)
│
├── admin/                 ← PAINEL ADMINISTRADOR
│   ├── index.php          ← Dashboard + aprovações pendentes
│   ├── vagas.php          ← Gerir todas as vagas
│   ├── empresas.php       ← Gerir todas as empresas
│   ├── candidatos.php     ← Lista de candidatos
│   ├── candidaturas.php   ← Todas as candidaturas
│   ├── relatorios.php     ← Estatísticas e relatórios
│   └── configuracoes.php  ← Cores, nome do site, config geral
│
├── empresa/               ← PAINEL EMPRESA
│   ├── index.php          ← Dashboard da empresa
│   ├── vagas.php          ← Minhas vagas
│   ├── nova_vaga.php      ← Publicar / editar vaga
│   ├── candidaturas.php   ← Ver e gerir candidaturas recebidas
│   └── perfil.php         ← Editar perfil da empresa + logo
│
├── candidato/             ← PAINEL CANDIDATO
│   ├── index.php          ← Dashboard do candidato
│   ├── candidaturas.php   ← Minhas candidaturas com progresso visual
│   ├── guardadas.php      ← Vagas guardadas (favoritos)
│   ├── perfil.php         ← Editar perfil pessoal + foto + CV
│   └── curriculo.php      ← Educação · Experiência · Competências
│
├── ajax/
│   └── guardar_vaga.php   ← Guardar/desfavoritar vaga (AJAX)
│
├── includes/
│   └── config.php         ← BD, helpers, auth, funções globais
│
├── assets/
│   ├── css/style.css      ← CSS completo responsivo (320px → 4K)
│   └── js/painel.js       ← Sidebar responsiva + touch gestures
│
├── uploads/
│   ├── logos/             ← Logos das empresas
│   ├── cvs/               ← CVs dos candidatos
│   └── fotos/             ← Fotos de perfil
│
└── database.sql           ← Schema completo + dados iniciais
```

---

## ⚙️ INSTALAÇÃO

### 1. Base de Dados
```bash
mysql -u root -p < database.sql
```

### 2. Configuração (includes/config.php)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // ← altere
define('DB_PASS', '');            // ← altere
define('DB_NAME', 'emprega_db');
define('BASE_URL', 'http://localhost/recrutamento/');
```

### 3. Permissões de Upload
```bash
chmod -R 755 uploads/
chmod -R 777 uploads/logos/
chmod -R 777 uploads/cvs/
chmod -R 777 uploads/fotos/
```

### 4. Servidor
Coloque a pasta em:
- **XAMPP**: `C:/xampp/htdocs/recrutamento/`
- **Linux**: `/var/www/html/recrutamento/`

Acesse: `http://localhost/recrutamento/`

---

## 🔐 CREDENCIAIS DE ACESSO INICIAL

| Tipo | Email | Senha |
|------|-------|-------|
| Admin | `admin@emprega.ao` | `password` |

> Para gerar o hash correto: `password_hash('Admin@2025', PASSWORD_DEFAULT)`

---

## 👥 PERFIS E FLUXO

### Candidato
1. Registo → Preenche perfil + CV
2. Pesquisa vagas com filtros
3. Guarda vagas favoritas
4. Candidata-se com carta de apresentação
5. Acompanha progresso das candidaturas

### Empresa
1. Registo → **Aguarda aprovação do admin** (configurável)
2. Preenche perfil da empresa + logo
3. Publica vagas → Aguarda aprovação do admin
4. Recebe candidaturas → Actualiza estados
5. Comunica via estados (entrevista, oferta, etc.)

### Administrador
1. Aprova/rejeita empresas
2. Aprova/rejeita/destaca vagas
3. Dá selo "Verificada" às empresas
4. Configura cores e nome do site
5. Visualiza relatórios e estatísticas

---

## 🎨 PERSONALIZAÇÃO DE TEMA

O Admin pode alterar as 3 cores principais em:
**Admin → Configurações → Cores e Tema**

As cores são guardadas na tabela `configuracoes` e aplicadas via CSS variables inline em cada página:
```html
<style>:root{
  --pri: #0a2540;  /* Cor primária */
  --ace: #e63946;  /* Cor de destaque */
  --sec: #457b9d;  /* Cor secundária */
}</style>
```

---

## 📱 RESPONSIVIDADE

| Tamanho | Breakpoint | Comportamento |
|---------|-----------|---------------|
| Mobile  | < 576px   | Sidebar fullscreen, menu hamburger |
| Tablet  | 576–991px | Sidebar slide-in, overlay |
| Desktop | 992px+    | Sidebar fixa permanente |
| TV/4K   | 1920px+   | Layout ampliado |

**Gestos touch:**
- Swipe direita (borda esquerda) → Abre sidebar
- Swipe esquerda → Fecha sidebar

---

## 🔢 ESTADOS DAS CANDIDATURAS

```
Enviada → Vista → Em Análise → Entrevista → Oferta → Aceite
                                          ↘ Rejeitada
```

A empresa atualiza o estado → Candidato recebe notificação automática.

---

## 📊 BASE DE DADOS (Tabelas principais)

| Tabela | Descrição |
|--------|-----------|
| `utilizadores` | Todos os utilizadores (admin/empresa/candidato) |
| `candidatos` | Perfis dos candidatos |
| `empresas` | Perfis das empresas |
| `vagas` | Ofertas de emprego |
| `candidaturas` | Ligação candidato ↔ vaga |
| `vagas_guardadas` | Favoritos dos candidatos |
| `notificacoes` | Notificações internas |
| `categorias` | 15 categorias pré-definidas |
| `provincias` | 20 províncias de Angola |
| `configuracoes` | Configurações do site |

---

## 🚀 FUNCIONALIDADES FUTURAS (sugestões)

- [ ] Email automático (PHPMailer) a cada mudança de estado
- [ ] Alertas de emprego por email (nova vaga na categoria X)
- [ ] Sistema de mensagens empresa ↔ candidato
- [ ] Exportar CV em PDF
- [ ] API REST para app mobile
- [ ] Login social (Google/LinkedIn)
- [ ] Planos pagos para empresas (destaque premium)

---

*Emprega v1.0 — Plataforma de Recrutamento para Angola*
