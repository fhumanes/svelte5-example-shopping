<script>
    import { Willow, Button } from '@svar-ui/svelte-core';
    import Toasts from './notification/Toasts.svelte'; // Asegúrate de que la ruta sea correcta

    import { auth } from './stores/auth.js';
    import { onDestroy } from 'svelte';

    import Login from './pages/Login.svelte';
    import Logout from './pages/Logout.svelte';

    import Header_1 from "./components/Header_1.svelte";
    import Header_2 from "./components/Header_2.svelte";
    import Header_3 from "./components/Header_3.svelte";
    import Inicio from "./pages/Inicio.svelte";
    import Productos from "./pages/Productos.svelte";
    import Usuarios from "./pages/Usuarios.svelte";
    import MiPerfil from "./pages/MiPerfil.svelte";
    import Registro from "./pages/Registro.svelte";
    import Secciones from "./pages/Secciones.svelte";
    import Altaengrupo from "./pages/AltaEnGrupo.svelte";
    import Grupos from "./pages/Grupos.svelte";

    import ADusuarios from "./pages/ADusuarios.svelte";
    import ADsecciones from "./pages/ADsecciones.svelte";
    import ADgrupos from "./pages/ADgrupos.svelte";
    import ADunidades from "./pages/ADunidades.svelte";


    let basepath = "/my-shopping"; // Ajusta esto según tu configuración de despliegue

    // Para gestión del Menú
    let menuAbierto = false;
    let vistaActual = '';

    let user = null;
    // let groupSelected = null;

    // Mapa de vistas → componente
    const vistas = {
      login: Login,
      logout: Logout,
      registro: Registro,
      inicio: Inicio,
      productos: Productos,
      usuarios: Usuarios,
      grupos: Grupos,
      miperfil: MiPerfil,
      secciones: Secciones,
      altaGrupo: Altaengrupo,
      ADusuarios: ADusuarios,
      ADsecciones: ADsecciones,
      ADgrupos: ADgrupos,
      ADunidades: ADunidades
    };

    function toggleMenu() {
        menuAbierto = !menuAbierto;
    }

    function cambiarVista(vista) {
        vistaActual = vista;
        menuAbierto = false;
    }

    // Autenticación de Usuarios
    let isAuthenticated = false;

    const unsubscribe = auth.subscribe(value => {
    isAuthenticated = value.isAuthenticated;
    user = value.user;

    vistaActual = isAuthenticated ? 'inicio' : 'login';
  });

  onDestroy(unsubscribe);

</script>
<Toasts />
  {#if isAuthenticated}
    {#if user.isAdministrator === '1'}
      <Header_3 {menuAbierto} {vistaActual} {toggleMenu} {cambiarVista} />
    {:else}
      <Header_2 {menuAbierto} {vistaActual} {toggleMenu} {cambiarVista} />
    {/if}
  {:else}
      <Header_1 {menuAbierto} {vistaActual} {toggleMenu} {cambiarVista} /> 
  {/if}
  <Willow>
    <div class="app-container">
        <main class="content-area">
          {#if vistas[vistaActual]}
            <!-- Renderiza el componente según el valor de vistaActual -->
            <svelte:component this={vistas[vistaActual]} {cambiarVista} />
            <!-- <{vistas[vistaActual]} {cambiarVista} /> -->
 
          {:else}
            <p>🚨------- Vista no encontrada ---------🚨</p>
          {/if}
        </main> 
    </div>
  </Willow>  


<style>
  main {
    position: fixed;
    top: 55px;      /* debajo de la cabecera fija // 55 desktop || 80 movil */
    left: 5px;        /* pegado al borde izquierdo */
    width: calc(100% - 10px); /* ocupa todo el ancho si quieres */
     /* min-height: calc(99vh - 55px); resto de la ventana */
    height: calc(100dvh - 55px); 
    overflow-y: auto;
    padding: 1rem;
  }
   @media (max-width: 768px) {
    main {
      top: 80px;      /* debajo de la cabecera fija // 55 desktop || 80 movil */
      /* min-height: calc(99vh - 80px);  resto de la ventana */
      height: calc(99dvh - 80px); 
      overflow-y: auto;
    }
  }

  .app-container {  
    display: flex;
    flex-direction: column;
    /* min-height: 97vh;  */
  } 

  .content-area {
    /* margin-top: 15px;  /* Ajusta según la altura de tu encabezado */
    flex-grow: 1; /* Permite que el área de contenido ocupe el espacio restante */
    /*  padding: 30px; */
    padding-top: 0px;
    padding-right: 30px;
    padding-bottom:30px;
    padding-left: 30px;
    background-color: #dfe1e2; /* Fondo similar al de tus modales */
  }
  @media (max-width: 768px) {
    .content-area {
      /* margin-top: 15px;  /* Ajusta según la altura de tu encabezado */
      flex-grow: 1; /* Permite que el área de contenido ocupe el espacio restante */
      /*  padding: 30px; */
      padding-top: 0px;
      padding-right: 3px;
      padding-bottom:3px;
      padding-left: 3px;
      background-color: #dfe1e2; /* Fondo similar al de tus modales */
    }
  }


  :global(.wx-sidearea) { /* Cambio del tamaño de área de edición */
    width: 300px;
  }
  
  :global(.wx-willow-theme) {
    --wx-table-select-background: #dfdadc;
    --wx-table-select-color: var(--wx-color-font);
    --wx-table-border: 1px solid #dfdadc;
    --wx-table-select-border: inset 3px 0 var(--wx-color-primary);
    --wx-table-header-border: var(--wx-table-border);
    --wx-table-header-cell-border: var(--wx-table-border);
    --wx-table-footer-cell-border: var(--wx-table-border);
    --wx-table-cell-border: var(--wx-table-border);
    --wx-header-font-weight: 600;
    --wx-table-header-background: #f2f3f7;
    --wx-table-fixed-column-border: 3px solid #e6e6e6;
    --wx-table-editor-dropdown-border: var(--wx-table-border);
    --wx-table-editor-dropdown-shadow: 0px 4px 20px rgba(44, 47, 60, 0.12);
    --wx-table-drag-over-background: var(--wx-background-alt);
    --wx-table-drag-zone-shadow: var(--wx-box-shadow);
   
  
    /* For Filter Builder */
    --wx-filter-value-color: var(--wx-color-primary); /* text value color in FilterBuilder*/
    --wx-filter-and-background: #fcba2e; /* background for the glue "and" logic button in FilterBuilder*/
    --wx-filter-or-background: #77d257; /* background for the glue "or" logic button in FilterBuilder*/
    --wx-filter-and-font-color: var(--wx-color-font); /* font color for the glue "and" logic button in FilterBuilder*/
    --wx-filter-or-font-color: var(--wx-color-font); /* font color for the glue "or" logic button in FilterBuilder*/
    --wx-filter-border: 1px solid #e6e6e6; /* filter border around filter blocks in  FilterEditor*/
  }
  /* Estilos globales para el área de edición */
  :global(.wx-sidearea) {
    background-color: rgb(193, 200, 211) !important;
    height: 100% IMPORTANT !important;
  }
  :global(.wx-field-control:not(:has(input,select, check, radio, .wx-richselect)) ) { /* Estilos para campos sin controles de entrada */
    background-color: white;
    min-height: 30px;
    align-content: center;
    padding-left: 5px;
  }
    :global(.wx-tabs .wx-active ) { /* Estilos para la solapa activa */
    background-color: white !important;
    /* background-color:#37a9ef !important;
    color: white !important; */
  }
  :global(.wx-sections) {
    text-align: left;
    --wx-field-width: 600px;
    margin: 12px 5px 0 5px;
  }
  @media (max-width: 768px) {
    :global(.wx-sections) {
      --wx-field-width: 250px;
      margin: 12px 5px 0 5px;
    }
  }
  </style>

  