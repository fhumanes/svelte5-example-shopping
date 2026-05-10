<script>
    import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow de aquí
    import { Editor } from 'wx-svelte-editor';
    import { registerEditorItem } from "wx-svelte-editor";
    import { Text, Select, DatePicker, RichSelect, TextArea } from "wx-svelte-core";
    import { es as coreEs } from "@svar-ui/core-locales";
    import { default as editorEs } from "../locales/esEditor.js";

    import Password from "../components/Password.svelte";

    // Sistema de notificación de las acciones
    import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
    import { addToast } from "../notification/store.js";

    import api from '../lib/api.js';                  // Importación de la API para las peticiones
    import { handleFatalError } from '../lib/errorHandlers.js';

    // import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL
    
    // Parámetros del menú
    // El orden en $props() NO importa para recibir
    // let { editorMode, editorValues, movies, themes , supports , handleSave, closeEdit } = $props();
    let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte
    
    // Definiciones del Editor -------------------------------------------------------------------------------------------------------------
    var editorMode = 'add';

    registerEditorItem("text", Text);
    registerEditorItem("textarea", TextArea);

    // registerEditorItem("password", Password);
    // registerEditorItem("select", RichSelect);
    // registerEditorItem("datepicker", DatePicker);
    
    const bottomBarEdit={ // Definición del bottomBar para modo add/edit
            items: [
            {
                comp: "button",
                type: "primary",
                text: "Guardar",
                id: "save",
            },
            ],
        };

    let editorValues = $state({
        name: '',
        description: ''
    });  


    let editorItems = [
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
            placeholder: "Breve descripción del Grupo",
            validationMessage: "Mínimo un carácter, máximo 200",
            validation: val => {
            const regEx = /^[\s\S]{1,200}$/; // Cualquier carcater, incluyendo saltos de línea
            return val && regEx.test(val);
            }
        }
    ];

    // Función para transformar los valores del editor a un formato adecuado para la API
    function transformLoginPayload(values) {
        const { name, description} = values;
        return {
            name,
            description
        };
    }

    // Función para proceder con la validación de "login"
    async	function handleSave(ev) {
        // console.log ("handleSave ev: ", ev);
        const values = ev.values; // Valores del formulario
        const payload = transformLoginPayload(values);
        try {
            const res = await api.post("/group",payload);
            // console.log("Respuesta de login:", res.data);

            addToast({
                message:'Registrado correctamente' ,
                type: 'info',
                dismissible: false,
                timeout: 2000
            });

            cambiarVista('inicio'); // Cambiar a la vista de contacto tras la creación del Grupo
            
        } catch (err) {
            // console.error("Error en operación de editor:", err);
            if (err.response?.status === 400) {
                addToast({
                    message: err.response ,
                    type: 'error',
                    dismissible: true,
                    timeout: 4000
                });
                editorValues = values; // Mantener los valores introducidos
                return
            } else {
                return handleFatalError({ err: err, cambiarVista });  // error fatal
            }
        }
    }


</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="formHeader">
    <h3> 🧺 Nuevo Grupo de Compra</h3>
  </div>
      <div>Has llegado a este punto porque todavía no estás dado de alta a ningún Grupo.
        <ul>
            <li>
                Puedes crear uno donde tú serás el administrador y luego invitar a otros usuarios a unirse.
            </li>
            <li>
            También puedes solicitar a otro usuario que te añada a su grupo. Para ello tendrás que 
            faciltarle tu email por cualquier medio externo a ese sistema.
            </li>
        </ul>

    </div>
  
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            focus={true}
            placement="normal"
            bottomBar = {editorMode === "view"?bottomBarView:bottomBarEdit}
            layout="default"
            autoSave={false}
            readonly={editorMode === "view"}
            items={editorItems}
            values={editorValues}
		    onsave={handleSave}

          />
        </Locale>
      </div>
    </div>
  </div>
</div>

<style>
  .formHeader {
    text-align: left;
    border-bottom:  1px solid #ccc; 
  }
  .variations .bg {
    background: var(--slate3);
    border-radius: 8px;
    padding: 3px;
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
  @media (max-width: 768px) {
    .variations > div {
      margin: 0 0px 2px 0px;
      width: 100%;
    }
  } 
	.bg {
		padding: 10px;
		height: 100%;
    width: 300px;
	}
  @media (max-width: 768px) {
    .bg {
      padding: 0px;
      height: 100%;
      width: 300px;
    }
  }

</style>