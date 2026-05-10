<script>
import { onMount } from 'svelte';
import { ModalArea } from "wx-svelte-core";
import { Grid } from 'wx-svelte-grid';
import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow
import { Editor } from 'wx-svelte-editor';
import { ContextMenu } from '@svar-ui/svelte-menu';
import { registerEditorItem } from "wx-svelte-editor";
import { Text } from "wx-svelte-core";

import { es as coreEs } from "@svar-ui/core-locales";
import { default as gridEs} from "../locales/esGrid.js";
import { default as editorEs } from "../locales/esEditor.js";

import Fa from 'svelte-fa';
import { faScaleBalanced } 
      from '@fortawesome/free-solid-svg-icons';

import Toasts from "../notification/Toasts.svelte";
import { addToast } from "../notification/store.js";
import api from '../lib/api.js';
import { handleFatalError } from '../lib/errorHandlers.js';
import { auth, group, logout } from '../stores/auth.js';

let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

let user = null;
let groupSelected = $state(null);

let gridRef = $state();
let selected = $state([]);
let records = $state([]);
let loading = $state(true);

let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("add");
let showDeleteConfirm = $state(false);
let itemToDelete = $state(null);

registerEditorItem("text", Text);

let editorItems = $derived([
{ key: "title", label: "Unidad", comp: "text", required: true, maxLength: 50 }
]);

// Control de permisos al inicializar la página
onMount(() => {  // Montar el componente
    //Acceso a los Stores y control de grupo seleccionado
    auth.subscribe(value => {  user = value.user  });   // Recogemos el usuario y el grupo del store
    // group.subscribe(value => { groupSelected = value});

    //  console.log("Grupo seleccionado en Grupos.svelte:", groupSelected);
    // console.log("Usuario en Grupos.svelte:", user);

    if (user.isAdministrator !== "1" ) {  // Control de si es Administrador
        addToast({
            message:'Para utilizar esta opción es necesario ser administrador.', 
            type: 'error',
            dismissible: true,
            timeout: 4000
        });
        cambiarVista('inicio'); // Si no hay grupo, volvemos a inicio
    } else {
        // Todo bien, cargamos las secciones
        fetchAllRecords();
    }
});

async function fetchAllRecords() {
try {
    const res = await api.get(`/measure`);
    // console.log('Respuesta de la API:', res);
    // El array real está aquí:
    const array = res.data.data;
   
    const recordsNormalized = array.map(({ id_unit_measure: id, ...rest }) => ({
        id,
        ...rest
    }));

    records = recordsNormalized;

} catch (err) {
    // console.error('Error al cargar secciones:', err);
    return handleFatalError({ err: err, cambiarVista });

} finally {
    loading = false;
}
}

function transformRecordPayload(values) {
// const { record } = values;
return values ;
}

async function handleEditorAction({ item, values }) {
// console.log("handleEditorAction item:", item);
// console.log("handleEditorAction values:", values);
const payload = transformRecordPayload(values);
// console.log("handleEditorAction payload:", payload);
try {
    if (item.id === "save") {
    if (editorMode === "add") {
        const res = await api.post(`/measure`, payload);
        values.id = res.data.id_support ?? Date.now();
        addToast({
        message:'Medida añadida correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
        });
        fetchAllRecords();
    } else if (editorMode === "edit") {
        await api.put(`/measure/${values.id}`, payload);
        addToast({
        message:'Medida actualizada correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
        });
        fetchAllRecords();
    }
    showEditor = false;
    } else if (item.id === "close" || item.id === "cancel") {
    showEditor = false;
    }
} catch (err) {
    // console.error("Error en operación de editor:", err);
    // console.log('Status:', err.response.status);
    // console.log('Mensaje:', err.response.data.message);
    return handleFatalError({ err: err, cambiarVista });
}
}

function confirmDelete(item) {
itemToDelete = item;
showDeleteConfirm = true;
}

async function proceedDelete() {
try {
    await api.delete(`/measure/${itemToDelete.id}`);
    records = records.filter(s => s.id !== itemToDelete.id);
    addToast({
    message:'Medida eliminada correctamente' ,
    type: 'success',
    dismissible: true,
    timeout: 2000
    });
} catch (err) {
    console.error("Error al eliminar medida", err);
    addToast({
    message: "No se pudo eliminar la medida.",
    type: 'error',
    dismissible: true,
    timeout: 0
    });
} finally {
    showDeleteConfirm = false;
    itemToDelete = null;
}
}

function cancelDelete() {
showDeleteConfirm = false;
itemToDelete = null;
}

const contextOptions = [
{ id: "add", text: "Agregar", icon: "wxi-plus" },
{ id: "edit", text: "Editar", icon: "wxi-edit" },
{ id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }
];

function handleContext(ev) {
const id = gridRef.getState().selectedRows[0];
const row = id ? gridRef.getRow(id) : null;

switch (ev.action?.id) {
    case "add":
    editorMode = "add";
    editorValues = {};
    showEditor = true;
    break;
    case "edit":
    if (row) {
        editorMode = "edit";
        editorValues = { ...row };
        showEditor = true;
    }
    break;
    case "delete":
    if (id) {
        const recordItem = records.find(s => s.id === id);
        if (recordItem) {
        confirmDelete(recordItem);
        }
    }
    break;
    default:
    console.warn("Acción no reconocida:", ev.action);
    break;
}
}

function resolver(id) {
if (id) gridRef.exec("select-row", { id });
return id;
}

function clearFilters() {
gridRef.exec("filter-rows", {});
}

function updateSelected() {
selected = gridRef.getState().selectedRows;
}

const columnStyle = col => {
if (col.id === "id") return "text-right";
return "";
};

const columns = $derived([
{ id: "id", header: "ID", width: 60, sort: true, resize: true },
{
    id: "title",
    header: [
    "Unidad",
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

// Botones del Editor
  const bottomBarView={ // Definición del bottomBar para modo view
        items: [
            {
                comp: "button",
                type: "primary",
                text: "Cerrar",
                id: "close",
            },
        ],
    };

    const bottomBarEdit={ // Definición del bottomBar para modo add/edit
    items: [
        {
            comp: "button",
            type: "primary",
            text: "Guardar",
            id: "save",
        },
                    {
            comp: "button",
            type: "normal",
            text: "Cancelar",
            id: "cancel",
        },
    ],
};
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><Fa icon={faScaleBalanced} /> Gestión de las Medidas de los Productos</h3>
  <div class="d-flex gap-2">
    <Button type="primary" text="🧹 Borrar filtros" onclick={clearFilters} />
    <Button type="primary" text="➕ Agregar Sección" onclick={() => {
      editorMode = "add";
      editorValues = {};
      showEditor = true;
    }} />
  </div>
  <div>Para el acceso a las acciones CRUD, botón derecho en el Grid</div>
</div>

{#if loading}
  <p class="text-center">Cargando soportes...</p>
{:else}
  <ContextMenu
    options={contextOptions}
    onclick={handleContext}
    at="point"
    resolver={resolver}
    api={gridRef}
  >
    <Locale words={{ ...gridEs, ...coreEs }}>
    <div class="custom-table">
      <Grid
        bind:this={gridRef}
        data={records}
        {columns}
        {columnStyle}
        pager={false}
        onselectrow={updateSelected}
      />
    </div>
    </Locale>
  </ContextMenu>
{/if}

{#if showEditor}
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            placement="sidebar"
            bottomBar = {editorMode === "view"?bottomBarView:bottomBarEdit}
            layout="default"
            autoSave={false}
            readonly={editorMode === "view"}
            items={editorItems}
            values={editorValues}
            onaction={handleEditorAction}
          />
        </Locale>
      </div>
    </div>
  </div>
{/if}

{#if showDeleteConfirm}
  <ModalArea>
    <div class="modal-content">
      <h3>¿Eliminar este registro?</h3>
      <p>Unidad: <b>{itemToDelete?.title}</b></p>
      <div class="actions">
        <Button type="danger" onclick={proceedDelete}>Confirmar</Button>
        <Button onclick={cancelDelete} color="secondary">Cancelar</Button>
      </div>
    </div>
  </ModalArea>
{/if}

<style>
  .custom-table {
    height: 440px; 
    max-width: 400px;
  }
  .modal-content {
    padding: 30px;
    text-align: center;
    background-color: #f1f5f9;
    border: 2px solid #f97316;
    border-radius: 0px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    font-family: "Segoe UI", "Roboto", sans-serif;
    max-width: 400px;
    margin: 0 auto;
  }
  .modal-content h3 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    color: #1e293b;
  }
  .modal-content p {
    margin: 10px 0;
    font-size: 1rem;
    color: #334155;
  }
  .actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
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
  .variations {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
	}
	.variations > div {
		margin: 0 20px 60px 20px;
		width: 400px;
	}
	.bg {
		border-top: 1px solid #ccc;
		padding: 10px;
		height: 100%;
    width: 300px;
	}
  :global(.wx-sidearea) {
    width: 300px;
  }
</style>