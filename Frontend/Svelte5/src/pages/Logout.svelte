<script>
  import { auth, group, logout } from '../stores/auth.js';
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow

  let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

  let user = $state('');
  let groupSelected = $state('');

  auth.subscribe(value => {
    user = value.user;
    // console.log("User en Logout.svelte:", user);
  });
  group.subscribe(value => {
    groupSelected = value;
    // console.log("Group en Logout.svelte:", groupSelected);
    // console.log("Group_id en Logout.svelte:", groupSelected?.id);
  });

  handleLogout(); // Cambio para Logout automático
  
  function handleLogout() {
    logout();               // Limpiar el estado de autenticación

    cambiarVista('login'); // Volver a la vista de login tras cerrar sesión
  }
</script>

<h2>Usuario conectado</h2> 
<p>
  Nombre: <b> {user?.name_surname} </b>, Login: <b>{user?.login}</b>
</p>
<Button type="primary" onclick={handleLogout}>
  Cerrar sesión
</Button>