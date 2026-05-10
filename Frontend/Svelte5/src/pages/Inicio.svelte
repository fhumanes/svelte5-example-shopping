<script>
  import { onMount } from 'svelte';
  // import { ModalArea } from "wx-svelte-core";
  import { Grid } from 'wx-svelte-grid';
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow

  import { Text, Select, DatePicker, RichSelect  } from "wx-svelte-core";

  import { es as coreEs } from "@svar-ui/core-locales";
  import { default as gridEs} from "../locales/esGrid.js";
  import { default as editorEs } from "../locales/esEditor.js";

  import Toasts from "../notification/Toasts.svelte";
  import { addToast } from "../notification/store";
  import api from '../lib/api.js';
  import { handleFatalError } from '../lib/errorHandlers.js';
  import { auth, logout, setActiveGroup } from '../stores/auth.js';

   // Parámetros del menú
  // El orden en $props() NO importa para recibir
  // let { editorMode, editorValues, movies, themes , supports , handleSave, closeEdit } = $props();
  let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

  let gridRef = $state();
  let selected = $state([]);
  let groups = $state([]);
  let loading = $state(true);

onMount(fetchAllRecords); // Llamar a la función al montar el componente

async function fetchAllRecords() {
    try {
        const res = await api.get('/group');
        // console.log('Respuesta de la API:', res);
        // El array real está aquí:
        const array = res.data.data;
        // Normalizamos id_group → id
        const groupsNormalized = array.map(({ id_group: id, ...rest }) => ({
            id,
            ...rest
        }));

        // LÓGICA DE 0 / 1 / VARIOS GRUPOS
        if (groupsNormalized.length === 0) {
          // Caso 0 grupos
          cambiarVista('altaGrupo'); // o lo que corresponda// o mostrar mensaje, o cambiar vista
          return;
        }

        if (groupsNormalized.length === 1) {
          // Caso 1 grupo → selección automática
          setActiveGroup(groupsNormalized[0]);           // Guardamos el grupo activo en el store de group
          cambiarVista('productos');             // Cambiamos a la vista de "secciones"
          return;
        }

        // Caso >1 grupo → mostrar GRID
        groups = groupsNormalized;
        // console.log('Grupos cargados:', groups);

    } catch (err) {
      // console.error('Error al cargar los grupos:', err);

      handleFatalError({err, cambiarVista}); // Usamos el manejador de errores fatal

    } finally {
      loading = false;
    }
  }
  // Función para actualizar la selección de filas en el Grid
  function updateSelected() {
    selected = gridRef.getState().selectedRows;
    let rowNum = selected[0];           // primer elemento seleccionado
    // console.log('Fila seleccionada:',  rowNum);
    let group = groups.filter(m => m.id === rowNum); // nos quedamos con el registro seleccionado
    // console.log('Grupo seleccionado:',  group);
    setActiveGroup(group[0]);           // Guardamos el grupo activo en el store de group
    cambiarVista('productos');             // Cambiamos a la vista de "secciones"
  }

  const columnStyle = col => {
    if (col.id === "id") return "text-right";
    return "";
  };

  const columns = $derived([
    { id: "id", header: "ID", width: 60, sort: true, resize: true },
    {
      id: "name",
      header: [
        "Grupo",
        {
          filter: {
            type: "text",
            config: { icon: "wxi-search", clear: true }
          }
        }
      ],
      flexgrow: 1,
      sort: true,
      resize: true
    }
  ]);
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>🧺 Seleccione Grupo para hacer la compra</h3>

</div>

{#if loading}
  <p class="text-center">Cargando grupos...</p>
{:else}

<Locale words={{ ...gridEs, ...coreEs }}>
<div class="custom-table">
    <Grid
    bind:this={gridRef}
    data={groups}
    {columns}
    {columnStyle}
    pager={false}
    onselectrow={updateSelected}
    />
</div>
</Locale>

{/if}


<style>
  .custom-table {
    height: 440px; 
    max-width: 400px;
  }
  :global(.wx-toast) {
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    font-weight: 500;
    font-family: "Segoe UI", sans-serif;
    padding: 10px 20px;
  }
  :global(.wx-cell.text-right) {
    text-align: right;
  }
  :global(.wx-cell.text-center) {
    text-align: center;
  }
</style>
