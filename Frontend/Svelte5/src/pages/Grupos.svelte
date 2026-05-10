<script>
import { onMount } from 'svelte';
import { ModalArea } from "wx-svelte-core";
import { Grid } from 'wx-svelte-grid';
import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow
import { Editor } from 'wx-svelte-editor';
import { ContextMenu } from '@svar-ui/svelte-menu';
import { registerEditorItem } from "wx-svelte-editor";
import { Text, Checkbox, Select, DatePicker, RichSelect, TextArea } from "wx-svelte-core";
import CustomReadonly from "../components/CustomReadonly.svelte";

import { es as coreEs } from "@svar-ui/core-locales";
import { default as gridEs} from "../locales/esGrid.js";
import { default as editorEs } from "../locales/esEditor.js";

import Toasts from "../notification/Toasts.svelte";
import { addToast } from "../notification/store";
import api from '../lib/api.js';
import { handleFatalError } from '../lib/errorHandlers.js';
import { toValidAPIDate, formatDateTimeToAPI, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL
import { auth, group, logout } from '../stores/auth.js';
import DatetimeCell from '../components/DatetimeCell.svelte';

import { faPeopleGroup } from '@fortawesome/free-solid-svg-icons';
    import Fa from 'svelte-fa';

let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

let user = $state(null);
let groupSelected = $state(null);

let gridRef = $state();
let selected = $state([]);
let records = $state([]);
let loading = $state(true);

let usersGroup = $state([]); // Usuarios del grupo seleccionado

let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("add");
let showDeleteConfirm = $state(false);
let itemToDelete = $state(null);

// Control de permisos al inicializar la página
onMount(() => {  // Montar el componente
    //Acceso a los Stores y control de grupo seleccionado
    auth.subscribe(value => {  user = value.user  });   // Recogemos el usuario y el grupo del store
    group.subscribe(value => { groupSelected = value});

    //  console.log("Grupo seleccionado en Grupos.svelte:", groupSelected);
    // console.log("Usuario en Grupos.svelte:", user);

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
    }
});

// Función para cargar todos los registros del GRID
async function fetchAllRecords() {
  try {
    const res = await api.get(`/group`);
    // console.log('Respuesta de la API:', res);
    // El array real está aquí:
    const array = res.data.data;
    // Normalizamos id_section_group → id

  const recordsNormalized = array
      .filter(record => record.user_administrator === user.id_user)
      .map(({ id_group: id, creation_date, ...rest }) => ({
          id,
          creation_date: createDateFromMySQL(creation_date),
          creation_date_text: formatDateToDDMMYYYY(createDateFromMySQL(creation_date)),
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

// Función para cargar los usuarios de un grupo
async function fetchUsers(id_group) {
    const id = id_group;
  try {
    const res = await api.get(`/userGroup/${id}`);   //recogemos los ususarios del grupo
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
    const recordsNormalized = array.map(({ user_id: id, name_surname: label,  ...rest }) => ({
        id,
        label,
        ...rest
    }));
    usersGroup = recordsNormalized;
    // console.log("Usuarios del grupo cargados:", usersGroup);

  } catch (err) {
    // console.error('Error al cargar usuarios:', err);
    return handleFatalError({ err: err, cambiarVista });

  } finally {
  }
}

// Función para transformar los valores del editor antes de enviarlos a la API
function transformRecordPayload(values) {
  let { id, name, description, creation_date, user_administrator } = values;

// console.log("Valores del editor a transformar:", values);
if (creation_date === undefined) {
    creation_date = new Date(); // Si no hay fecha, ponemos la actual
}
if (user_administrator === undefined) {
    user_administrator= user.id_user; // Si no hay administrador, ponemos el usuario actual
}
  return {
    id,
    name, 
    description,
    creation_date: formatDateTimeToAPI(creation_date),
    user_administrator
  };
}

// Manejo de acciones del editor
async function handleEditorAction({ item, values }) {
  let payload = transformRecordPayload(values);
  // console.log("Payload a enviar:", payload);

  if (item.id === "save") {
    if (editorMode === "add") {
    
        api.post(`/group`, payload) // Añadimos grupo
        .then(res => {

            addToast({
            message:'Grupo añadido correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
            });
            fetchAllRecords();
            showEditor = false;
        })
        .catch(err => {
            return handleFatalError({ err: err, cambiarVista });         
        });
    } 
    else if (editorMode === "edit") {
    
        api.put(`/group/${payload.id}`, payload) // Editamos grupo
        .then(res => {

            addToast({
            message:'Grupo actualizado correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
            });
            fetchAllRecords();
            showEditor = false;
        })
        .catch(err => {
            return handleFatalError({ err: err, cambiarVista });         
        });
    }     
  } else if (item.id === "close" || item.id === "cancel") {
  showEditor = false;
  }
}

// Funciones para Confirmar la eliminar un registro
function confirmDelete(item) {
itemToDelete = item;
showDeleteConfirm = true;
}

// Proceder a eliminar el registro
async function proceedDelete() {
try {
    await api.delete(`/group/${itemToDelete.id}`);
    records = records.filter(s => s.id !== itemToDelete.id);
    addToast({
    message:'Grupo eliminada correctamente' ,
    type: 'success',
    dismissible: true,
    timeout: 2000
    });
} catch (err) {
    console.error("Error al eliminar Grupo", err);
    addToast({
    message: "No se pudo eliminar el Grupo.",
    type: 'error',
    dismissible: true,
    timeout: 0
    });
} finally {
    showDeleteConfirm = false;
    itemToDelete = null;
}
}

// Función de cancelar la operación de Borrado
function cancelDelete() {
showDeleteConfirm = false;
itemToDelete = null;
}

// Registro de componentes personalizados para el Editor
registerEditorItem("text", Text);
registerEditorItem("datetime", DatePicker); 
registerEditorItem("select", RichSelect);
registerEditorItem("memo", TextArea);
registerEditorItem("button", Button);
registerEditorItem("datetime", DatetimeCell);   // Componente personalizado para campo datetime

registerEditorItem("readonly", CustomReadonly);   // Componente personalizado para Acción VIEW o Readonly


let editorItems = $derived([
{ 
    key: "name",
    label: "Nombre del Grupo",
    comp: "text",
    required: true,
    maxLength: 60,
    placeholder: "Entre 5 y 50 caracteres",
    validationMessage: "Válido: Entre 5 y 50 caracteres",
    validation: val => {
    const regEx = /^.{5,50}$/;
    return val && regEx.test(val);
    }
},
{ 
    key: "description",
    label: "Descipción",
    comp: "textarea",
    required: true,
    maxLength: 200,
    CustomReadonly: "Memo", 
    placeholder: "Breve descripción del Grupo",
    validationMessage: "Mínimo un carácter, máximo 200",
    validation: val => {
    const regEx = /^[\s\S]{1,200}$/; // 
    return val && regEx.test(val);
    }
},    
{ 
    key:  "creation_date", // (editorMode === "edit"? "creation_date_text2" : "creation_date" ) , 
    label: "Fecha de Creación", 
    comp: "datetime", 
    time: true, 
    format: "%d/%m/%Y", /// "%d/%m/%Y %H:%i",
    // readonly: true , 
    CustomReadonly: "Datetime", 
    hidden: (editorMode === "add"? true : false )
},
{ 
    key: "user_administrator", 
    label: "Administrador", 
    comp: "select", 
    options: usersGroup , 
    required: true , 
    hidden: (editorMode === "edit"? false : true )
}  
]);


// Definición de acciones del menú contextual del G
const contextOptions = [
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    { id: "view", text: "Ver", icon: "wxi-eye" },
    { id: "edit", text: "Editar", icon: "wxi-edit" },
    { id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }
    ];

//  Acciones del menú contextual 
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
    editorMode = "edit";
    fetchUsers(id);  // Cargamos los usuarios del grupo antes de editar
    // Edición de la fecha para mostrar en el editor
    const date = new Date(row.creation_date);
    row.creation_date_text2 = date.toLocaleString(); // Convertimos la fecha para el editor

    editorValues = row;
    showEditor = true;
    break;

    case "view":
    editorMode = "view";
    // row.description = nl2br(row.description); // Formateamos saltos de línea en memo
    editorValues =row;
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

// Función de selección de un registro del GRID
function resolver(id) {
if (id) gridRef.exec("select-row", { id });
// const record = records.find(r => r.id === id);
// menuAction = record.isUpdatable === '1' ? true : false;
// console.log("resolver id:", id, "isUpdatable:", record?.isUpdatable, "menuAction:", menuAction);
return id;
}

// Función para el borrados de filtros del GRID
function clearFilters() {
gridRef.exec("filter-rows", {});
}

// Función para obtener los Id?s de los registros seleccionados
function updateSelected() {
selected = gridRef.getState().selectedRows;
}

// Alimeación de columnas del GRID
const columnStyle = col => {
if (col.id === "id") return "text-right";
if (col.id === "creation_date_text") return "text-center";
return "";
};

// Definición de columnas del Grid
const columns = $derived([
{ id: "id", header: "ID", width: 60, sort: true, resize: true },
{
    id: "name",
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
    id: "creation_date_text",
    header: "Fecha de Creación",
    sort: false,
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
  <h3><Fa icon={faPeopleGroup} /> Gestión de Grupos del usuario: "{user?.name_surname}"</h3>
  <div class="d-flex gap-2">
    <Button type="primary" text="🧹 Borrar filtros" onclick={clearFilters} />

    <Button type="primary" text="➕ Agregar Grupo" onclick={() => {
      editorMode = "add";
      editorValues = {};
      showEditor = true;
    }} />

  </div>
     <div>Para el acceso a las acciones CRUD, botón derecho en el Grid</div>
    <div style="height: 0.5rem;"></div>
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
        <div style="height: 440px; max-width: 500px;">
        <Grid
            bind:this={gridRef}
            
            readonly_save={editorMode === "view"}
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
            bottomBar = {editorMode === "view"?bottomBarView:bottomBarEdit}
            placement="sidebar"
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
      <p>Grupo: <b>{itemToDelete?.name}</b></p>
      <div class="actions">
        <Button type="danger" onclick={proceedDelete}>Confirmar</Button>
        <Button onclick={cancelDelete} color="secondary">Cancelar</Button>
      </div>
    </div>
  </ModalArea>
{/if}

<style>
/*
  .custom-table {
    height: 440px; 
    max-width: 500px;
  }
*/
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