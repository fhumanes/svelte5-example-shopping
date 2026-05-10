<script>
import { onMount } from 'svelte';
import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow
import { Editor } from 'wx-svelte-editor';

import { registerEditorItem } from "wx-svelte-editor";
import { Text, Checkbox, Select, DatePicker, RichSelect, TextArea } from "wx-svelte-core";
import CustomReadonly from "../components/CustomReadonly.svelte";

import { es as coreEs } from "@svar-ui/core-locales";
import { default as editorEs } from "../locales/esEditor.js";

import { Tabs } from "@svar-ui/svelte-core";

import Toasts from "../notification/Toasts.svelte";
import { addToast } from "../notification/store";
import api from '../lib/api.js';
import { handleFatalError } from '../lib/errorHandlers.js';
import { toValidAPIDate, formatDateTimeToAPI, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL
import { auth, group, logout } from '../stores/auth.js';
import DatetimeCell from '../components/DatetimeCell.svelte';

import { faUser } from '@fortawesome/free-solid-svg-icons';
import Fa from 'svelte-fa';

let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte

let user = $state(null);
let groupSelected = $state(null);


let usersGroup = $state([]); // Usuarios del grupo seleccionado

let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("view"); // "passw", "edit", "view"
let record = $state([]); // Registro original del editor

// Definición de las pestañas para la selección de acción
let tabId = $state(1);
const selectTabs = [
    { id: 1, label: "Información" },
    { id: 2, label: "Actualizar" },
    { id: 3, label: "Cambio de Password" }
];
function handleSelectTabsChange({ value }) {
    showEditor = false;                 // Para "destruir" el formulario
    tabId = value;
    switch (tabId) {
        case 1:
            editorMode = "view";
            editorValues = {};
            editorValues = record;
            showEditor = true;
            break;
        case 2:
            editorMode = "edit";
            editorValues = record;
            showEditor = true;
            break;
        case 3:
            editorMode = "passw";
            editorValues = {};
            showEditor = true;
            break;
    }
}

// Control de permisos al inicializar la página
onMount(() => {  // Montar el componente
    //Acceso a los Stores y control de grupo seleccionado
    auth.subscribe(value => {  user = value.user  });   // Recogemos el usuario y el grupo del store
    group.subscribe(value => { groupSelected = value});

    fetchRecord(); 
});

// Función para cargar todos los registros del GRID
async function fetchRecord() {
    try {
        const res = await api.get(`/userInfo`);
        const array = res.data.data;
        // Normalizamos id_section_group → id
    const recordsNormalized = array
        .map(({ id_user: id, date_last_change, active, isAdministrator, ...rest }) => ({
            id,
            date_last_change: createDateFromMySQL(date_last_change),
            active: active === '1'? true : false,
            isAdministrator: isAdministrator === '1' ? true : false,
            ...rest
        }));

    record = recordsNormalized[0];
    // console.log ('Registro cargado:', record);
    editorMode = "view";
    editorValues = record;
    showEditor = true;
    } catch (err) {
        // console.error('Error al cargar usuarios:', err);
        return handleFatalError({ err: err, cambiarVista });
    } finally {
}
}

// Función para transformar los valores del editor antes de enviarlos a la API
function transformRecordPayload(values) {
  let { login, name_surname, email} = values;
  return {
    login,
    name_surname, 
    email
  };
}

// Manejo de acciones del editor
async function handleEditorAction({ item, values }) {
  // console.log("Acción del editor:", item , values);
/*
   // Si no hay item, no es una acción de botón → salimos 
   if (!item || !item.id) { 
    return; 
}
*/ 
 //  if (item.id === "save") {  // Está fallando y no sé por qué...
    if (editorMode === "edit") {
            // Validación de edición 
            let payload = transformRecordPayload(values);
            // console.log("Payload a enviar:", payload);     
            api.put(`/userUpdate`, payload) // Editamos grupo
            .then(res => {
                addToast({
                message:'Usuario actualizado correctamente' ,
                type: 'success',
                dismissible: true,
                timeout: 2000
                });
                fetchRecord();
            })
            .catch(err => {
                return handleFatalError({ err: err, cambiarVista });         
            });
        }
        if (editorMode === "passw") {
        // Validación de passwords
        const required = ["passwordOld", "passwordNew1", "passwordNew2"];   // Todos los campos son obligatorios
        const ok = required.every(key => key in values);                    // y están presentes
        if (!ok) { 
            addToast({
            message:'Se deben completar todos los campos' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
            });
            return;
        }
        if (values.passwordNew1 !== values.passwordNew2) {                  // Las 2 nuevas password deben coincidir
            addToast({
                message: 'Error: Las nuevas contraseñas no coinciden',
                type: 'error',
                dismissible: true,
                timeout: 4000
            });
            return;
        }
        let payloadPassw = {
            passwordOld: values.passwordOld,
            passwordNew1: values.passwordNew1,
            passwordNew2: values.passwordNew2
        };
        api.put(`/userPassword`, payloadPassw) // Cambiamos password
        .then(res => {
            addToast({
            message:'Password cambiado correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
            });
            fetchRecord();         
        })
        .catch(err => {
            return handleFatalError({ err: err, cambiarVista });         
        }); 
        } 
   /* } 
 
    if (item.id === "close" || item.id === "cancel") {
    editorMode = "view";
    editorValues = record;
    showEditor = false;
    }
    */

}

function handleClick(ev) {
    // need to check that there are changes and close editor after successful validation
    // otherwise, even if "save" event is not triggered, editor will be closed anyway
    // but we still can close editor if there are not any changes
// console.log ("handleClick ev: ", ev);

const changes = ev.changes;
const values = ev.values;
const item = ev.item;

if (item.id === "cancel" || item.id === "close") {
    tabId = 1;  // Volvemos a la pestaña de Información
    editorMode = "view";
    editorValues = record;
    showEditor = true;
}
}

// Registro de componentes personalizados para el Editor
registerEditorItem("text", Text);
// registerEditorItem("datetime", DatePicker); 
// registerEditorItem("select", RichSelect);
// registerEditorItem("memo", TextArea);
// registerEditorItem("button", Button);
registerEditorItem("datetime", DatetimeCell);   // Componente personalizado para campo datetime
registerEditorItem("readonly", CustomReadonly);   // Componente personalizado para Acción VIEW o Readonly

    // Modo VIEW y EDIT: datos generales 
    const editorItems = $derived([
        {
            key: "login",
            label: "Login",
            comp: "text",
            required: true,
            maxLength: 16,
            placeholder: "Entre 5 y 16 caracteres",
            validationMessage: "Válido: Entre 5 y 16 caracteres",
            validation: val => {
            const regEx = /^.{5,16}$/;
            return val && regEx.test(val);
            },
        },
        { 
            key: "name_surname",
            label: "Nombre",
            comp: "text",
            required: true,
            maxLength: 100,
            CustomReadonly: "Text", 
            placeholder: "Identificación del Usuario",
            validationMessage: "Mínimo 5 carácter, máximo 100",
            validation: val => {
            const regEx = /^[\s\S]{5,100}$/; // 
            return val && regEx.test(val);
            },
        },  
        {
            key: "email",
            label: "Email",
            comp: "text",
            required: true,
            maxLength: 100,
            placeholder: "Formato: usuario@dominio.com",
            validationMessage: "Válido: usuario@dominio.com",
            validation: val => {
            const regEx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return val && regEx.test(val);
            },
        }, 
        {                        
            key: "user_last_change_text",
            label: "Usuario del último cambio",
            comp: "text",
            hidden: (editorMode === "edit"? true:false)
        },
        {                   
            key: "date_last_change",
            label: "Fecha del último cambio",
            comp: "datetime",
            CustomReadonly: "Datetime", 
            hidden: (editorMode === "edit"? true:false)
        }, 
        {
            key: "active",
            label: "Activo?",
            comp: "checkbox",
            CustomReadonly: "Boolean",
            hidden: (editorMode === "edit"? true:false)
        },
        {
            key: "isAdministrator",
            label: "Es Administrador?",
            comp: "checkbox",
            CustomReadonly: "Boolean",
            hidden: (editorMode === "edit"? true:false) 
        }
]);

    // Modo PASSW: solo gestión de password
    const editorItemsPassw = $derived([ 
        {
            key: "passwordOld",
            label: "Password Actual",
            comp: "text",
            type: "password",
            required: true,
            maxLength: 16,
            placeholder: "Letras y números, mínimo 5",
            validationMessage: "Válido: Letras y números, mínimo 5",
            validation: val => {
            const regEx = /^[A-Za-z0-9]{5,}$/;
            return val && regEx.test(val);
            },
        },
        {             
            key: "passwordNew1",
            label: "Password Nueva",
            comp: "text",
            type: "password",
            required: true,
            maxLength: 16,
            placeholder: "Letras y números, mínimo 5",
            validationMessage: "Válido: Letras y números, mínimo 5",
            validation: val => {
            const regEx = /^[A-Za-z0-9]{5,}$/;
            return val && regEx.test(val);
            },

        },
        {             
            key: "passwordNew2",
            label: "Repetición de Password Nueva",
            comp: "text",
            type: "password",
            required: true,
            maxLength: 16,
            placeholder: "Letras y números, mínimo 5",
            validationMessage: "Válido: Letras y números, mínimo 5",
            validation: val => {
            const regEx = /^[A-Za-z0-9]{5,}$/;
            return val && regEx.test(val);
            }                               
        }
    ]);

const bottomBarEdit={ // Definición del bottomBar para modo add/edit
    items: [
        {
        comp: "button",
        type: "primary",
        text: "Enviar",
        id: "save",
        },
        {
        comp: "button",
        type: "secondary",
        text: "Cerrar",
        id: "close",
        },
    ],
    };
const bottomBarView={ // Definición del bottomBar para modo  view
    items: [
        {

        },
    ],
    };

</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><Fa icon={faUser} /> Información del Usuario conectado</h3>
 
  <div class="wx-filter-bar"> 
    <strong>Acción:</strong> 
    <Tabs value={tabId} options={selectTabs} onchange={handleSelectTabsChange} />
  </div> 

{#if showEditor && (  editorMode === "view" ) }

  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            focus={true}
            placement="inline"}
            layout="default"
            autoSave={false}
            bottomBar= {editorMode === "view" ? bottomBarView : bottomBarEdit }
            readonly= true
            items={editorItems}
            values={editorValues}
            onsave={handleEditorAction}
            onaction={handleClick}
          />
        </Locale>
      </div>
    </div>
  </div>
{/if}

{#if showEditor && ( editorMode === "edit" ) }

  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            focus={true}
            placement="inline"}
            layout="default"
            autoSave={false}
            bottomBar= {editorMode === "view" ? bottomBarView : bottomBarEdit }
            
            items={editorItems}
            values={editorValues}
            onsave={handleEditorAction}
            onaction={handleClick}
          />
        </Locale>
      </div>
    </div>
  </div>
{/if}

{#if showEditor && editorMode === "passw" }

  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            focus={true}
            placement="inline"}
            layout="default"
            autoSave={false}
            bottomBar= {bottomBarEdit}

            items={editorItemsPassw}
            values={editorValues}
            onsave={handleEditorAction}
            onaction={handleClick}
          />
        </Locale>
      </div>
    </div>
  </div>
{/if}



</div>

<style>
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