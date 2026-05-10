<script>
  import Fa from 'svelte-fa';
  import { faKey, faUserPlus, faBasketShopping } from '@fortawesome/free-solid-svg-icons';
  
  let { menuAbierto, vistaActual, toggleMenu, cambiarVista } = $props();

  // Estado local para abrir/cerrar el submenú "Otras"
  let subMenuOtras = $state(false);

  function irAVista(vista) {
    cambiarVista(vista);
    // Opcional: cerrar submenú y menú principal tras navegar (útil en móvil)
    subMenuOtras = false;
    // menuAbierto = false; // si quieres cerrarlo también
  }
</script>

<nav>
  <div class="logo"><Fa icon={faBasketShopping} /> Shopping</div>

  <button class="menu-toggle" onclick={toggleMenu} aria-label="Abrir menú" type="button">
    ☰
  </button>

  <ul class:abierto={menuAbierto}>
    <!-- Login -->
    <li>
      <button
        class="nav-btn"
        class:activo={vistaActual === 'login'}
        onclick={() => irAVista('login')}
      >
        <Fa icon={faKey} /> Login
      </button>
    </li>
    <li>
        <button
          class="nav-btn submenu-btn"
          class:activo={vistaActual === 'registro'}
          onclick={() => irAVista('registro')}
        >
          <Fa icon={faUserPlus} /> Registro
        </button>
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

  /* Submenú 
  .submenu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;                 / 🔥 Se abre hacia la izquierda /
    background-color: #444;
    padding: 0.4rem 0;
    border-radius: 4px;
    min-width: 180px;
    max-width: 250px;         / 🔥 Evita que se salga /
    white-space: normal;      / 🔥 Permite que el texto haga salto de línea /
    z-index: 1100;
    flex-direction: column;
    gap: 0.2rem;
  }

  .submenu.abierto {
    display: flex;
  }

  .submenu {
    position: static;    / en móvil, que “empuje” hacia abajo /
    background-color: transparent;
    padding: 0.2rem 0 0.2rem 1rem;
    min-width: auto;
  }
 */
  .submenu-btn {
    font-size: 0.9rem;
    padding: 0.3rem 0.8rem;
    width: 100%;
    text-align: left;
  }

  .submenu-btn:hover {
    background-color: #555;
  }

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

    .submenu-btn {
      padding-left: 0;
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