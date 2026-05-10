<script>
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow de aquí
  import { Editor } from 'wx-svelte-editor';
  import { registerEditorItem } from "wx-svelte-editor";
  import { Text, Select, DatePicker, RichSelect } from "wx-svelte-core";
  import { es as coreEs } from "@svar-ui/core-locales";
  import { default as editorEs } from "../locales/esEditor.js"

  import Fa from 'svelte-fa';
  import { faBasketShopping, faCircleChevronDown, faCircleChevronRight, faCarrot, faLayerGroup, faUserGroup,faRightToBracket,faUser, faBroom, faPlusCircle} 
        from '@fortawesome/free-solid-svg-icons';

  // Sistema de notificación de las acciones
  import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
  import { addToast } from "../notification/store.js";

  import api from '../lib/api.js';                  // Importación de la API para las peticiones
  import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL

  import FileField from "../components/FileField.svelte";
  
  // Parámetros del DataGrid
  // El orden en $props() NO importa para recibir
  let { editorMode, editorValues, products, sections , measures , handleSave, closeEdit } = $props();

  // Definiciones del Editor -------------------------------------------------------------------------------------------------------------

  registerEditorItem("text", Text);
  registerEditorItem("number", Text);
  registerEditorItem("select", RichSelect);
  registerEditorItem("datepicker", DatePicker);
  registerEditorItem("file", FileField);
  
  let updated = false; // Para controlar si se ha actualizado el formulario

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
  // Opciones derivadas
  let sectionsOptions = $derived(
    sections.map(s => ({ id: s.id_section_group, label: s.section }))
  );

  let measuresOptions = $derived(
    measures.map(m => ({ id: m.id_unit_measure, label: m.title }))
  );


  let editorItems = $derived([
    { key: "section_group_id", 
      label: "Sección",
      comp: "select",
      options: sectionsOptions,
      required: true
    },
    { key: "name", 
      label: "Nombre", 
      comp: "text", 
      required: true, 
      maxLength: 60 
    },
    { key: "amount", 
      label: "Cantidad", 
      comp: "number", 
      required: true,
       validation: val => {
          const regEx = /^\d{1,4}(\.\d{1,3})?$/;
          return val && regEx.test(val);
        },
        validationMessage: "Introduce un número válido (máx. 4 enteros y 3 decimales)" 
    },
    { key: "unit_measure_id",
      label: "Unidad",
      comp: "select",
      options: measuresOptions,
      required: true
    },
    { key: "file", 
      comp: "file", 
      cell:  FileField,
      label: "Foto", 
      required: false 
    }
  ]);

 let editorItemsView = $derived([
    { key: "section", 
      label: "Sección",
      comp: "text",
      readonly: true,

    },
    { key: "name", 
      label: "Nombre", 
      comp: "text", 
      readonly: true, 
    },
    { key: "amount", 
      label: "Cantidad", 
      comp: "number",
      readonly: true, 
    },
    { key: "measure",
      label: "Unidad",
      comp: "text",
      readonly: true,

    },
    { key: "file", 
      comp: "file", 
      label: "Foto", 
      readonly: true,
      required: false 
    }
  ]);



// Manejo de acciones del editor
  function onchange(ev) {
    //console.log(`field ${ev.key} was changed to ${ev.value}`);
    // console.log("all not saved changes", ev.update);
    updated = true; // Marcamos que se ha actualizado el formulario
  }

	function handleClick( ev) {
		// need to check that there are changes and close editor after successful validation
		// otherwise, even if "save" event is not triggered, editor will be closed anyway
		// but we still can close editor if there are not any changes
    // console.log ("handleClick ev: ", ev);

    const changes = ev.changes;
    const values = ev.values;
    const item = ev.item;

		if (item.id === "save" && changes.length === 0 && Object.keys(values).length == 0  && editorMode == "add") {

        addToast({
            message:'Se deben completar los datos del formlario o cliquear Cancelar' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
          });
        console.warn("ADD - No hay cambios para guardar.");
    }

    if (item.id === "save" && updated === false && editorMode == "edit") {

        addToast({
            message:'Se deben modificar algún cambio o cliquear Cancelar' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
          });
        console.warn("EDIT - No hay cambios para guardar.");
    }

		if (item.id === "cancel" || item.id === "close") closeEdit();
	}


</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="formHeader">
    <h3><Fa icon={faCarrot} /> Gestión de Productos ({editorMode})</h3>
  </div>
  
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            focus={true}
            placement="inline"
            bottomBar = {editorMode === "view"?bottomBarView:bottomBarEdit}
            layout="default"
            autoSave={false}
            readonly_save={editorMode === "view"}
            items= {editorMode === "view"?editorItemsView:editorItems}  
            values={editorValues}
            onaction={handleClick}
		        onsave={handleSave}
            onchange={onchange}
          />
        </Locale>
      </div>
    </div>
  </div>
</div>

<style>
  .formHeader {
    text-align: center;
  }
  .variations .bg {
    background: var(--slate3);
    border-radius: 8px;
    padding: 8px;
    display: inline-block;
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

</style>