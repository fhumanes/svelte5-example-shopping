<script>
    import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow de aquí
    import { Editor } from 'wx-svelte-editor';
    import { registerEditorItem } from "wx-svelte-editor";
    import { Text, Select, DatePicker, RichSelect } from "wx-svelte-core";
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
    registerEditorItem("password", Password);
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
        nombre: '',
        login: '',
        password: '',
        email: ''
    });  


    let editorItems = [
        {
            key: "nombre",
            label: "Nombre",
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
            key: "login",
            label: "Login",
            comp: "text",
            required: true,
            maxLength: 16,
            placeholder: "Solo letras y números (6–16)",
            validationMessage: "Válido: Solo letras y números (6–16)",
            validation: val => {
            const regEx = /^[A-Za-z0-9]{6,16}$/;
            return val && regEx.test(val);
            }
        },
        {
            key: "password",
            label: "Password",
            comp: "password",
            required: true,
            maxLength: 16,
            placeholder: "Letras y números, mínimo 5",
            validationMessage: "Válido: Letras y números, mínimo 5",
            validation: val => {
            const regEx = /^[A-Za-z0-9]{5,}$/;
            return val && regEx.test(val);
            }
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
            }  
        }
    ];

    // Función para transformar los valores del editor a un formato adecuado para la API
    function transformLoginPayload(values) {
        const { nombre, login, password, email} = values;
        return {
            nombre: values.nombre,
            login,
            password,
            email
        };
    }

    // Función para proceder con la validación de "login"
    async	function handleSave(ev) {
        // console.log ("handleSave ev: ", ev);
        const values = ev.values; // Valores del formulario
        const payload = transformLoginPayload(values);
        try {
            const res = await api.post("/userRegister",payload);
            // console.log("Respuesta de login:", res.data);

            addToast({
                message:'Registrado correctamente' ,
                type: 'info',
                dismissible: false,
                timeout: 2000
            });

            cambiarVista('login'); // Cambiar a la vista de contacto tras el login
            
        } catch (err) {
            // console.error("Error en operación de editor:", err);
            addToast({
                message: err.response?.data?.message || 'Error en el registro' ,
                type: 'error',
                dismissible: true,
                timeout: 4000
            });
            editorValues = values; // Mantener los valores introducidos
        }
    }


</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="formHeader">
    <h3> 🧗Registro de Nuevos Usuarios</h3>
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