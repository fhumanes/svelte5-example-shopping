<script>
import { onMount } from 'svelte';
import { ModalArea } from "wx-svelte-core";
import { Grid } from 'wx-svelte-grid';
import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow
import { Editor } from 'wx-svelte-editor';
import { ContextMenu } from '@svar-ui/svelte-menu';
import { registerEditorItem } from "wx-svelte-editor";
import { Text, Checkbox } from "wx-svelte-core";
import CheckCell from "../components/CheckCell.svelte";

import { es as coreEs } from "@svar-ui/core-locales";
import { default as gridEs} from "../locales/esGrid.js";
import { default as editorEs } from "../locales/esEditor.js";

import Fa from 'svelte-fa';
import { faUserGroup } 
      from '@fortawesome/free-solid-svg-icons';

import Toasts from "../notification/Toasts.svelte";
import { addToast } from "../notification/store";
import api from '../lib/api.js';
import { handleFatalError } from '../lib/errorHandlers.js';
import { auth, group, logout } from '../stores/auth.js';

let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

let user = null;
let groupSelected = $state(null);
let isAdministrator = $state(false);    // ¿Es administrador del grupo?

let gridRef = $state();
let selected = $state([]);
let records = $state([]);
let loading = $state(true);

let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("add");
let showDeleteConfirm = $state(false);
let itemToDelete = $state(null);

let menuAction = $state(false); // false: Menú sin Delte  y true: Menú con Delete



onMount(() => {  // Montar el componente
    //Acceso a los Stores y control de grupo seleccionado
    auth.subscribe(value => {  user = value.user  });   // Recogemos el usuario y el grupo del store
    group.subscribe(value => { groupSelected = value});

    if (!groupSelected) {  // Control de grupo seleccionado
        addToast({
            message:'Es necesario definir un grupo antes de continuar.', 
            type: 'error',
            dismissible: false,
            timeout: 4000
        });
        cambiarVista('inicio'); // Si no hay grupo, volvemos a inicio
    } else {
        // Todo bien, cargamos las secciones
        fetchAllRecords();
        isAdministrator = user?.id_user === groupSelected?.user_administrator; // ¿Es administrador del grupo?
    }
});

async function fetchAllRecords() {
  try {
    const res = await api.get(`/userGroup/${groupSelected.id}`);
    // console.log('Respuesta de la API:', res);
    // El array real está aquí:
    const array = res.data.data;
    // Normalizamos id_section_group → id
    /*
    const recordsNormalized = array.map(({ id_user_group: id, isUpdatable, ...rest }) => ({
        id,
        isUpdatable: isUpdatable === "1",
        ...rest
    }));
    */
    const recordsNormalized = array.map(({ id_user_group: id,  ...rest }) => ({
        id,
        ...rest
    }));
    records = recordsNormalized;

  } catch (err) {
    // console.error('Error al cargar usuarios:', err);
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
  let payload = transformRecordPayload(values);

  if (item.id === "save") {
    if (editorMode === "add") {
      // chequeamos que el email no esté ya en el grupo y que sea correcto
      let email = values.email?.trim();
      const emailExists = records.some(r => r.email.trim() === email);
      if (emailExists) {
          addToast({
              message:`El email ${email} ya existe en el grupo.` ,
              type: 'error',
              dismissible: true,
              timeout: 4000
          });
          return;
      }
      // verificamos que el email existe en la base de datos de usuarios
      api.get(`/user/${email}`)
        .then(res => {
          // console.log('Respuesta de la API usuario:', res);
          payload = { id_user: res.data.data.id_user }; // normalizamos email  
          
          api.post(`/userGroup/${groupSelected.id}`, payload) // Añadimos usuario al grupo
            .then(res2 => {

                addToast({
                message:'Usuario añadido correctamente' ,
                type: 'success',
                dismissible: true,
                timeout: 2000
                });
                fetchAllRecords();
                showEditor = false;
            })
            .catch(err2 => {
                return handleFatalError({ err: err2, cambiarVista });         
            });
        })
        .catch(err => {
          return handleFatalError({ err: err, cambiarVista });
        });
    } 
      
  } else if (item.id === "close" || item.id === "cancel") {
  showEditor = false;
  }
}

function confirmDelete(item) {
itemToDelete = item;
showDeleteConfirm = true;
}

async function proceedDelete() {
try {
    await api.delete(`/userGroup/${groupSelected.id}/${itemToDelete.id}`);
    records = records.filter(s => s.id !== itemToDelete.id);
    addToast({
    message:'Usuario eliminada correctamente' ,
    type: 'success',
    dismissible: true,
    timeout: 2000
    });
    // Ver si el usuario deleteado es el ususario logueado
    console.log("Usuario eliminado id_user:", itemToDelete, "Usuario logueado id_user:", user);
    if (itemToDelete.user_id === user.id_user) {  
        // Si es así, hacemos logout
        logout();
        cambiarVista('login');
    }
} catch (err) {
    console.error("Error al eliminar usuario", err);
    addToast({
    message: "No se pudo eliminar el usuario.",
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

registerEditorItem("text", Text);
registerEditorItem("checkbox", CheckCell);

let editorItems = $derived([
{ key: "email", label: "Email", comp: "text", required: true, maxLength: 70 },
]);


const contextOptionsTrue = [
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    // { id: "edit", text: "Editar", icon: "wxi-edit" },//
    { id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }
    ];
const contextOptionsFalse = [
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    ];
const contextOptionsDelete = [
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
   
    case "delete":
    if (id) {
        const recordItem = records.find(s => s.id === id);
        if (recordItem) {
        confirmDelete(recordItem);
        }
    }
    break;
    default:
    // console.warn("Acción no reconocida:", ev.action);
    break;
}
}

function resolver(id) {
if (id) gridRef.exec("select-row", { id });
const record = records.find(r => r.id === id);
menuAction = record.isUpdatable === '1' ? true : false;
// console.log("resolver id:", id, "isUpdatable:", record?.isUpdatable, "menuAction:", menuAction);
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
if (col.id === "isUpdatable") return "text-center";
return "";
};

const columns = $derived([
{ id: "id", header: "ID", width: 60, sort: true, resize: true },
{
    id: "name_surname",
    header: [
    "Nombre",
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
},
{
    id: "email",
    header: [
    "Email",
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
},
{
    id: "isUpdatable",
    header: "Actualizable?",
    cell: CheckCell,
    resize: true,
    width: 110
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
  <h3><Fa icon={faUserGroup} /> Gestión de Usuarios del Grupo: "{groupSelected?.name}"</h3>
  <div class="d-flex gap-2">
    <Button type="primary" text="🧹 Borrar filtros" onclick={clearFilters} />
    {#if isAdministrator}
    <Button type="primary" text="➕ Agregar Usuario" onclick={() => {
      editorMode = "add";
      editorValues = {};
      showEditor = true;
    }} />
    {/if}
  </div>
    {#if isAdministrator}
        <div>Para el acceso a las acciones CRUD, botón derecho en el Grid</div>
    {:else}
       <div style="height: 0.5rem;"></div>
    {/if}
</div>

{#if loading}
  <p class="text-center">Cargando soportes...</p>
{:else}
    {#if isAdministrator}
    <ContextMenu
        options={menuAction ? contextOptionsTrue : contextOptionsFalse}
        onclick={handleContext}
        at="point"
        resolver={resolver}
        api={gridRef}
    >
        <Locale words={{ ...gridEs, ...coreEs }}>
        <div style="height: 440px; max-width: 500px;">
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
    {:else}
     <ContextMenu
        options={menuAction ? contextOptionsDelete :[{ }] }
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
      <p>Usuario: <b>{itemToDelete?.name_surname}</b></p>
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
    max-width: 500px;
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