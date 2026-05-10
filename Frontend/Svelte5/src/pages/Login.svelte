<script>
    import Fa from 'svelte-fa';
  import { faKey, faUserPlus } from '@fortawesome/free-solid-svg-icons';

  import { Button, Locale } from '@svar-ui/svelte-core'; // Eliminamos Willow de aquí
  import { Editor } from 'wx-svelte-editor';
  import { registerEditorItem } from "wx-svelte-editor";
  import { Text, Select, DatePicker, RichSelect } from "@svar-ui/svelte-core";
  import { es as coreEs } from "@svar-ui/core-locales";
  import { default as editorEs } from "../locales/esEditor.js";

  import Password from "../components/Password.svelte";

  // Sistema de notificación de las acciones
  import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
  import { addToast } from "../notification/store.js";

  import api from '../lib/api.js';                  // Importación de la API para las peticiones

  import { login } from '../stores/auth.js';

  import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL
  
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
  
  let updated = false; // Para controlar si se ha actualizado el formulario

  const bottomBarEdit={ // Definición del bottomBar para modo add/edit
        items: [
          {
            comp: "button",
            type: "primary",
            text: "Enviar",
            id: "save",
          },
            /*          {
            comp: "button",
            type: "normal",
            text: "Cancelar",
            id: "cancel",
          }, */
        ],
      };


  let editorValues = {
    username: '',
    password: ''
  };  


  let editorItems = [
    { key: "username", label: "Login", comp: "text", required: true, maxLength: 16 },
    // { key: "password", label: "Password", comp: "text", required: true,  maxLength: 16, type: "password" }
    { key: "password", label: "Password", comp: "password", required: true,  maxLength: 16}
  ];


// Función para transformar los valores del editor a un formato adecuado para la API
function transformLoginPayload(values) {
  const { username, password} = values;
  return {
    login: values.username,
    password
  };
}

// Función para proceder con la validación de "login"
  async	function handleSave(ev) {
    // console.log ("handleSave ev: ", ev);
    const values = ev.values; // Valores del formulario
    const payload = transformLoginPayload(values);
    try {
          const res = await api.post("/login",payload);

          // console.log("Respuesta de login:", res.data);

          let tokenUser = res.data.tokenUser;
          // console.log("Token de usuario:", tokenUser);
          let dataUser = res.data.dataUser;
          // console.log("Datos de usuario:", dataUser);

          const success = login(res.data.tokenUser, res.data.dataUser);

          addToast({
            message:'Identificado correctamente' ,
            type: 'info',
            dismissible: false,
            timeout: 2000
          });

          cambiarVista('inicio'); // Cambiar a la vista de contacto tras el login
          
    } catch (err) {
      console.error("Error en operación de editor:", err);
      addToast({
        message:'Credenciales incorrectas' ,
        type: 'error',
        dismissible: true,
        timeout: 4000
      });
    }
	}


  function onchange(ev) {
    // console.log(`field ${ev.key} was changed to ${ev.value}`);
    // console.log("all not saved changes", ev.update);
    updated = true; // Marcamos que se ha actualizado el formulario
  }

	function handleClick(ev) {
		// need to check that there are changes and close editor after successful validation
		// otherwise, even if "save" event is not triggered, editor will be closed anyway
		// but we still can close editor if there are not any changes
    // console.log ("handleClick ev: ", ev);

    const changes = ev.changes;
    const values = ev.values;
    const item = ev.item;

		if (item.id === "save" && changes.length === 0 && Object.keys(values).length == 0  && editorMode == "add") {

        addToast({
            message:'Se deben completar los datos del formlario' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
          });
        console.warn("ADD - No hay cambios para guardar.");
    }

		// if (item.id === "cancel" || item.id === "close") closeEdit();
	}


</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="formHeader">
    <h3><Fa icon={faKey} /> Identificación</h3>
  </div>
  
  <div class="login-container">
    <div class="login-left">
      <div class="variations">
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
                onaction={handleClick}
                onsave={handleSave}
                onchange={onchange}
              />
            </Locale>
          </div>
        </div>
      </div>
      <div class="login-right">
          <img src="assets/logo_empresa.png" alt="Logo empresa" />
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

.login-container {
  display: flex;
  flex-direction: row;
  align-items: stretch;
  justify-content: center;
  gap: 2rem;
}

/* Columna izquierda (formulario) */
.login-left {
  flex: 1;
}

/* Columna derecha (imagen) */
.login-right {
  flex: 1;
  display: flex;
  justify-content: left;
  align-items: center;
}

/* 📱 Vista móvil */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column; /* ← Aquí ocurre la magia */
  }

  .login-right {
    margin-top: 1.5rem; /* Separación visual */
    justify-content: center;
  }
}

  .login-right img {
      width: 100%;
      height: auto;
      object-fit: contain;       /* mantiene proporción */
  }

  .login-right img {
      max-width: 80%;
      height: auto;
  }


</style>
