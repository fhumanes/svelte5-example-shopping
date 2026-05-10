<script> 
  import Fa from 'svelte-fa';
  import { faBasketShopping, faCircleChevronDown, faCircleChevronRight, faCarrot, faLayerGroup, faUserGroup,faRightToBracket,faUser, faPeopleGroup, faScaleBalanced } 
        from '@fortawesome/free-solid-svg-icons';

  let  {menuAbierto, vistaActual, toggleMenu, cambiarVista} = $props(); // Recibimos las props desde nav.svelte

  // Estado local para abrir/cerrar el submenú "Otras"
  let subMenu1 = $state(false);
  let subMenu2 = $state(false);
  let subMenu3 = $state(false);

  function irAVista(vista) {
      cambiarVista(vista);
      // Opcional: cerrar submenú y menú principal tras navegar (útil en móvil)
      subMenu1 = false;
      subMenu2 = false;
      subMenu3 = false;
      // menuAbierto = false; // si quieres cerrarlo también
  }

</script>
<nav>
  <div class="logo"><Fa icon={faBasketShopping} /> Shopping</div>
  <button class="menu-toggle" onclick={toggleMenu} aria-label="Abrir menú" type="button">
    ☰
  </button>
  <ul class:abierto={menuAbierto}>
    <li>
      <button
        class="nav-btn"
        class:activo={vistaActual === 'inicio'}
        onclick={() => irAVista('inicio')}
      >
        <Fa icon={faBasketShopping} /> Inicio
      </button>
    </li>
    <li>
      <button
        class="nav-btn"
        class:activo={vistaActual === 'productos'}
        onclick={() => irAVista('productos')}
      >
        <Fa icon={faCarrot} /> Productos
      </button>
    </li>
    <!-- Submenu 1 -->
    <li>
      <button
        class="nav-btn"
        type="button"
        onclick={() => {
            subMenu1 = !subMenu1;
            subMenu2 = false;
            subMenu3 = false;
        }}
      >
      {#if subMenu1}
          <Fa icon={faCircleChevronDown} />
      {:else}
          <Fa icon={faCircleChevronRight} />
      {/if}
      Administración
      </button>

      <div class="submenu" class:abierto={subMenu1}>
        <button
          class="nav-btn"
          class:activo={vistaActual === 'secciones'}
          onclick={() => irAVista('secciones')}
        >
          <Fa icon={faLayerGroup} /> Mis Secciones
        </button>

      <button
        class="nav-btn"
        class:activo={vistaActual === 'usuarios'}
        onclick={() => irAVista('usuarios')}
      >
        <Fa icon={faUserGroup} /> Mis Usuarios
      </button>

      <button
        class="nav-btn"
        class:activo={vistaActual === 'grupos'}
        onclick={() => irAVista('grupos')}
      >
        <Fa icon={faPeopleGroup} /> Mis Grupos 
      </button>

      </div>
    </li>

    <!-- Submenu 3 -->
    <li>
      <button
        class="nav-btn"
        type="button"
        onclick={() => {
            subMenu3 = !subMenu3;
            subMenu1 = false;
            subMenu2 = false;
        }}
      >
      {#if subMenu3}
          <Fa icon={faCircleChevronDown} />
      {:else}
          <Fa icon={faCircleChevronRight} />
      {/if}
      AD Gestión
      </button>

      <div class="submenu" class:abierto={subMenu3}>

      <button
        class="nav-btn"
        class:activo={vistaActual === 'ADusuarios'}
        onclick={() => irAVista('ADusuarios')}
      >
        <Fa icon={faUserGroup} /> AD Usuarios
      </button>

      <button
        class="nav-btn"
        class:activo={vistaActual === 'ADgrupos'}
        onclick={() => irAVista('ADgrupos')}
      >
        <Fa icon={faPeopleGroup} /> AD Grupos 
      </button>

      <button
        class="nav-btn"
        class:activo={vistaActual === 'ADsecciones'}
        onclick={() => irAVista('ADsecciones')}
      >
        <Fa icon={faLayerGroup} /> AD Secciones
      </button>
      
      <button
        class="nav-btn"
        class:activo={vistaActual === 'ADunidades'}
        onclick={() => irAVista('ADunidades')}
      >
        <Fa icon={faScaleBalanced} /> AD Unidades 
      </button>

      </div>
    </li>

    <!-- Submenu 2 -->
    <li>
      <button
        class="nav-btn"
        type="button"
        onclick={() => {
            subMenu2 = !subMenu2;
            subMenu1 = false;
            subMenu3 = false;
        }}
      >
      {#if subMenu2}
          <Fa icon={faCircleChevronDown} />
      {:else}
          <Fa icon={faCircleChevronRight} />
      {/if} Usuario
      </button>

      <div class="submenu" class:abierto={subMenu2}>
        <button
          class="nav-btn"
          class:activo={vistaActual === 'miperfil'}
          onclick={() => irAVista('miperfil')}
        >
          <Fa icon={faUser} /> Mi Perfil
        </button>

        <button
          class="nav-btn"
          class:activo={vistaActual === 'logout'}
          onclick={() => irAVista('logout')}
        >
          <Fa icon={faRightToBracket} /> Logout
        </button>

      </div>
    </li>
 

  </ul>
</nav>


<style>
  * {
    box-sizing: border-box;
  }

  nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #333;
    color: white;
    padding: 0.5rem 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
  }

  .logo {
    font-weight: bold;
    font-size: 1.2rem;
  }

  .menu-toggle {
    font-size: 1.5rem;
    cursor: pointer;
    background: none;
    border: none;
    color: white;
  }

  ul {
    list-style: none;
    display: flex;
    gap: 1rem;
    margin: 0;
    padding: 0;
  }

  li {
    position: relative;     /* clave para posicionar el submenú */
    display: flex;
    align-items: center;
  }

  button.nav-btn {
    background: none;
    border: none;
    color: inherit;
    font: inherit;
    cursor: pointer;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    text-align: left;
  }

  button.nav-btn:hover {
    background-color: #555;
  }

  button.nav-btn.activo {
    background-color: #007acc;
  }

  /* Submenú */
  .submenu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;                 /* 🔥 Se abre hacia la izquierda */
    background-color: #444;
    padding: 0.4rem 0;
    border-radius: 4px;
    min-width: 180px;
    max-width: 250px;         /* 🔥 Evita que se salga */
    white-space: normal;      /* 🔥 Permite que el texto haga salto de línea */
    z-index: 1100;
    flex-direction: column;
    gap: 0.2rem;
  }

  .submenu.abierto {
    display: flex;
  }

  /*
  .submenu-btn {
    font-size: 0.9rem;
    padding: 0.3rem 0.8rem;
    width: 100%;
    text-align: left;
  }

  .submenu-btn:hover {
    background-color: #555;
  }

  .submenu-btn {
    padding-left: 0;
  }
  */
  @media (max-width: 768px) {
    ul {
      flex-direction: column;
      align-items: flex-start;
      padding-left: 1rem;
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      background-color: #444;
      display: none;
    }

    ul.abierto {
      display: flex;
    }

    li {
      width: 100%;
      flex-direction: column;
      align-items: flex-start;
    }

    .submenu {
      position: static;    /* en móvil, que “empuje” hacia abajo */
      background-color: transparent;
      padding: 0.2rem 0 0.2rem 1rem;
      min-width: auto;
    }

  }

  .menu-toggle {
    display: none;
  }

  @media (max-width: 768px) {
    .menu-toggle {
      display: block;
    }
  }
</style>