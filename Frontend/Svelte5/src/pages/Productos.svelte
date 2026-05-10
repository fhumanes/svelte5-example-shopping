<script>
  import { onMount } from 'svelte';
  import { ModalArea } from "@svar-ui/svelte-core"; // Ventana de Confirmación
  import { Grid } from 'wx-svelte-grid';
  import { Button, Locale } from '@svar-ui/svelte-core'; // Eliminamos Willow de aquí
  import { Editor } from 'wx-svelte-editor';
  import { ContextMenu } from '@svar-ui/svelte-menu';

  import Fa from 'svelte-fa';
  import { faBasketShopping, faCircleChevronDown, faCircleChevronRight, faCarrot, faLayerGroup, faUserGroup,faRightToBracket,faUser, faBroom, faPlusCircle, faEyeSlash, faEye } 
        from '@fortawesome/free-solid-svg-icons';
 
  import { Text, Select, DatePicker, RichSelect } from "wx-svelte-core";
  import { SideArea } from "@svar-ui/svelte-core";
  import { es as coreEs } from "@svar-ui/core-locales";
  import { default as gridEs} from "../locales/esGrid.js";
  import { default as editorEs } from "../locales/esEditor.js";
  import { default as filterEs } from "../locales/esFilter.js";
  // Filter external
  import { Tabs } from "@svar-ui/svelte-core";
  import { FilterBar, createFilter, getOptions, getFilters, createArrayFilter } from "wx-svelte-filter";
  import * as XLSX from "xlsx"
  //
  // Sistema de notificación de las acciones
  import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
  import { addToast } from "../notification/store";

  import { handleFatalError } from '../lib/errorHandlers.js';
  import { auth, group } from '../stores/auth.js';    // Importamos el store de autenticación y grupo
  import api from '../lib/api.js';                  // Importación de la API para las peticiones
  import {generarPDF} from '../lib/pdfGenerator.js';  // Informe PDF

  import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL

  import ProductosForm from './ProductosForm.svelte';      // Formulario de edición de Productos
  import CheckCell from '../components/CheckCell.svelte';
  import ImageCell from '../components/ImageCell.svelte';

  let { cambiarVista } = $props();   // Recibimos la función cambiarVista desde App.svelte
  // Información de la conexión: Grupo y Usuario
  let user = null;
  let groupSelected = $state(null);

  // Definiciones y código para la gestión del DataGrid
  let gridRef = $state();
  let selected = $state([]);
  let products = $state([]);
  let sections = $state([]);
  let measures = $state([]);

  let loading = $state(true);    // Estado de carga para mostrar un mensaje mientras se cargan los datos

  let showDeleteConfirm = $state(false);
  let productToDelete = $state(null);

  let productToChange = $state(null); // Producto a cambiar su estado de comprado/no comprado

  let menuRef = $state();         // Referencia al menú contextual

  let columnHidden = $state(false); // Para controlar columnas ocultas

  // Definición de las columnas del Grid ------------------------------------------------------------------------------------
  const columns = $derived([
    { id: "id", header: "ID", width: 50, sort: true, resize: true, hidden: columnHidden },
    {
      id: "section",
      header: [
        "Sección"
      ],
      // flexgrow: 1,
      width: 200,
      sort: true,
      resize: true,
      hidden: columnHidden
    },
    {
      id: "name",
      header: [
        "Nombre"
      ],
      // flexgrow: 1,
      width: columnHidden ? 185 : 200,
      sort: true,
      resize: true
    },
    {
      id: "amount",
      header: [
        "Cantidad"
      ],
      type: "number",
      sort: true,
      width: columnHidden ? 60 : 80,
      resize: true,

    },
    {
      id: "measure",
      type: "text",
      header: [
        "Unidad"
      ],
      //flexgrow: 1,
      width:  columnHidden ? 50 : 85,
      sort: true,
      resize: true
    },
    {
      id: "file",
      header: 'foto',
      type: "custom",
      width: 80,
      sort: false,
      resize: true,
      hidden: columnHidden,
      cell: ImageCell
    },
    {
      id: "purchased",
      header: '🧺',
      type: "custom",
      width: 45,
      sort: true,
      resize: true,
      cell: CheckCell
    }
  ]);

  // Función para resolver el ID del producto seleccionado en el Grid
  function resolver(id) {
    if (id) gridRef.exec("select-row", { id });
    return id;
  }
  // Función para cargar todos los datos al inicio y reload de datos después de actualizaciones de Base de Datos
  async function fetchAllData() {
      try {
          const [resProductos, resMeasures, resSections] = await Promise.all([
              api.get('/product/'+groupSelected.id),
              api.get('/measure'),
              api.get('/secctionGroup/'+groupSelected.id)
          ]);
          products = resProductos.data.data.map(m => {
            // const dateObj = createDateFromMySQL(m.date_last_buy);
            // const dateObj_text = formatDateToDDMMYYYY(dateObj);
            // console.log("Fecha de compra original:", m.date_last_buy, "Objeto Date:", dateObj, "Texto formateado:", dateObj_text);
            return {
              id: m.id_product,
              section: m.section,
              section_group_id: m.section_group_id,
              name: m.name,
              amount: parseFloat(m.amount),
              // date_last_buy: dateObj,
              // date_last_buy_text: dateObj_text,
              date_last_buy:  createDateFromMySQL(m.date_last_buy),
              date_last_buy_text: formatDateToDDMMYYYY(createDateFromMySQL(m.date_last_buy)),
              file: {
                filename: m.photo_file,
                mimetype: m.photo_mime,
                size: 1,
                base64: m.photo,
              },
              measure: m.title,
              unit_measure_id: m.unit_measure_id,
              purchased: m.purchased
 
            };
          });
          // console.log("Productos Cargados:", products);
          filterDataSet();                                     // Aplicar filtro a los datos cargados
          measures = resMeasures.data.data;
          sections = resSections.data.data;

      } catch (err) {
          // console.error('Error al cargar datos:', err);
          handleFatalError({err, cambiarVista});              // Manejo del error fatal
      } finally {
          loading = false;
      }
  }
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
            // Todo bien, cargamos Los Productos, medidas y secciones
            fetchAllData();
        }
  });


  // Función para actualizar la selección de filas en el Grid
  function updateSelected() {
    selected = gridRef.getState().selectedRows;
  }
  // Estilo de las columnas del Grid
  const columnStyle = col => {
    if (col.id === "id") return "text-right";
    if (col.id === "amount") return "text-right";
    if (col.id === "date_last_buy") return "text-center";
    return "";
  };

  // Menú conextual del DataGrid
    const contextOptions = [
    { id: "switch", text: " Cambia 🧺 ", icon: "wxi-arrows-h" },
    { id: "view", text: "Ver", icon: "wxi-eye" },
    { id: "edit", text: "Editar", icon: "wxi-edit" },
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    { id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }

  ];

  function handleContext(ev) {
      const id = gridRef.getState().selectedRows[0];
      const row = id ? gridRef.getRow(id) : null;
     /*
      if (!Array.isArray(editorItems)) {
        console.warn("editorItems no es un array:", editorItems);
      }
    */
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
        case "view":
          if (row) {
            // console.log("Valores del registro: ",row);
            editorMode = "view";
            editorValues = { ...row };
            editorValues = {
              ...row,
              startDate: row.startDateText
            };
            showEditor = true;
          }
          break;
          case "switch":
          if (id) {
            const product = products.find(m => m.id === id);
            if (product) {
              togglePurchased(product)   // Cambio el estado de comprado/no comprado

              const purchased = productToChange.purchased === '0'?'1':'0'; // Cambio el estado de comprado/no comprado
              let cambios = {};
              if (purchased==='0') {
                cambios = { purchased: '0' };
                } else {
                const hoy = new Date();                       // Fecha de hoy
                const hoy_text = formatDateToDDMMYYYY(hoy);   // Fecha formateada

                cambios = { purchased: '1', date_last_buy: hoy, date_last_buy_text: hoy_text  };
              }
              updateById(product.id, cambios);                        // Actualizo el estado en el Grid  
              filterDataSet();                                     // Aplicar filtro a los datos cargados
            }
          }
          break;
        case "delete":
          if (id) {
            const product = products.find(m => m.id === id);
            if (product) {
              productToDelete = product;
              showDeleteConfirm = true;
            }
          }
          break;
        default:
          console.warn("Acción no reconocida:", ev.action);
          break;
      }
    }

// Definiciones para el Cambio de estado comprado/no comprado -------------------------------------------------------------
function updateById(id, cambios) {
  // console.log("Actualizando producto ID:", id, "con cambios:", cambios);

  const index = products.findIndex(item => item.id === id);
  if (index === -1) return;

  products = [
    ...products.slice(0, index),
    { ...products[index], ...cambios }, // solo se mezclan los campos nuevos
    ...products.slice(index + 1)
  ];
  // console.log("Producto actualizado:", products);
}
/*
Ejemplo: 
updateById(2, { edad: 26 });
updateById(3, { activo: false, nombre: "Luis Alberto" });
*/
// Definiciones para el Cambio de estado comprado/no comprado ------------------------------------------------
async function togglePurchased(product) {
    productToChange = product;
    const purchased = productToChange.purchased === '0'?'1':'0'; // Cambio el estado de comprado/no comprado
    if (productToChange) {

      try {
        await api.put(`/product/${groupSelected.id}/${productToChange.id}/${purchased}`);
        addToast({
          message:`Producto  "${productToChange.name}"", marcado como  ${purchased === '1' ? 'comprado' : 'no comprado'}` ,
          type: 'success',
          dismissible: true,
          timeout: 2000
        });
        // fetchAllData(); // Refresco los datos
      } catch (err) {
        // console.error("Error al cambiar estado de comprado/no comprado:", err);
        handleFatalError({err, cambiarVista});              // Manejo del error fatal
      } finally {
        // productToChange = null;
      }
    }
  }

// Definiciones del Editor -------------------------------------------------------------------------------------------------------------
let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("add")

// Función para transformar los valores del editor a un formato adecuado para la API
function transformProductPayload(values) {
  const { name, amount, date_last_buy, section_group_id, unit_measure_id, file } = values;
  // console.log("valor de variable fecha de compra: ",date_last_buy);

  if (typeof date_last_buy === "undefined") 
    {
        let date_last_buy = new Date;             // Por defecto
    }
  const formattedDate = toValidAPIDate(date_last_buy);
  console.log("información de fichero foto: ".file);
  return {
    name, 
    amount,
    date_last_buy: formattedDate,
    section_group_id, 
    unit_measure_id,
    photo: file?.base64,
    photo_file: file?.filename,
    photo_mime: file?.mimetype,
    photo_size: file?.size
  };
}

async	function handleSave(ev) {
	const values = ev.values;
    // console.log("Guardando valores en SAVE:",values);
    const payload = transformProductPayload(values);
    // console.log("Guardando Payload en SAVE:",payload);
    try {
        if (editorMode === "add") {
          // Elimino campos que no quiero en el JSON
          const { date_last_buy, ...payload2 } = payload;

          const res = await api.post("/product/"+groupSelected.id,payload2);
          values.id = res.data.id_product ?? Date.now();
          addToast({
            message:'Producto añadido correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });
          fetchAllData();
        } else if (editorMode === "edit") {
          await api.put(`/product/${groupSelected.id}/${values.id}`, payload);
          addToast({
            message:'Producto actualizado correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });   
          fetchAllData();
        }
        showEditor = false
    } catch (err) {
      // console.error("Error en operación de editor:", err)
      // showEditor = false;
      handleFatalError({err, cambiarVista});              // Manejo del error fatal
    }
	}

function closeEdit() { // Función para cerrar el editor desde MovieForm
  showEditor = false;
}

// Definiciones para Delete -------------------------------------------------------------------------------------------------------------

// Función para confirmar la eliminación del producto
  function confirmDelete(product) {
    productToDelete = product;
    showDeleteConfirm = true;
  }
// Función para proceder con la eliminación del producto
  async function proceedDelete() {
    // console.log("Registro a deletetear:",productToDelete);
    try {
      await api.delete(`/product/${groupSelected.id}/${productToDelete.id}`);
      products = products.filter(m => m.id !== productToDelete.id); // Eliminación local Original
      filteredProducts = filteredProducts.filter(m => m.id !==  productToDelete.id); // Eliminación local Filtrado
      addToast({
        message:'Producto eliminado correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
      });
    } catch (err) {
      // console.error("Error al eliminar película:", err);
      handleFatalError({err, cambiarVista});              // Manejo del error fatal
    } finally {
      showDeleteConfirm = false;
      productToDelete = null;
    }
  }
// Función para cancelar la eliminación de una película
  function cancelDelete() {
    showDeleteConfirm = false;
    productToDelete = null;
  }

  // Comjunto de Productos a Visualizar -----------------------------------------------------------------------------
  let dataSetId = $state(1);  // 1: No comprados, 2: Hoy Comprados, 3: Comprados
  let productsDataSet = $state([]);

  const dataSets = [
		{ id: 1, label: "Pendientes" },
		{ id: 2, label: "C. Hoy" },
    { id: 3, label: "Comprados" }
	];  
  function handleDataSetChange({ value }) {
    dataSetId = value;
    filterDataSet();
  }

   // Filtra las Producto según el filtro actual
 function filterDataSet() {
  // Filtrado de Productos según el conjunto seleccionado
  if (dataSetId === 1) {          // No comprados
    productsDataSet = products.filter(m => m.purchased === '0');
  } else if (dataSetId === 2) {   // Comprados hoy
    const hoy = new Date();
    const hoy_text = formatDateToDDMMYYYY(hoy);
    productsDataSet = products.filter(m => m.purchased === '1' && m.date_last_buy_text === hoy_text);
    console.log("fercha de comparación: :", hoy_text, "Productos filtrados:", productsDataSet);
  } else if (dataSetId === 3) {   // Comprados
    productsDataSet = products.filter(m => m.purchased === '1');
  } else {
    productsDataSet = products; // Por defecto, todos los productos
  }
  filterProducts();                                    // Aplicar filtro a los datos cargados
 }

  // Filter External -------------------------------------------------------------------------------------------------  
	let filterId = $state(1);
	const filterTabs = [
		{ id: 1, label: "Todos los campos" },
		{ id: 2, label: "Por Sección" }
	];

  let fieldFilter = $derived([
            { id: "name", type: "text", label: "Nombre",  placeholder: "Buscar por Nombre" },
            { id: "section", type: "text", label: "Sección", options: getOptions(products, "section"), placeholder: "Buscar por Sección" },
            { id: "measure", type: "text", label: "Unidad", options: getOptions(products, "measure"), placeholder: "Buscar por Unidad" }       
          ]);

 let filteredProducts = $state(null);
 let filter = $state(null);

function handleFilterChange({ value }) {
    filterId = value;
    gridRef.exec("filter-rows", { filter: null });
}
// Apartado de Filtrado de Productos  ------------------------------------------------------------

 // Filtra las Producto según el filtro actual
 function filterProducts() {
  // console.log("Filtrando productos con el filtro:", filter);
  if (!filter) {
    filteredProducts = productsDataSet; // Si no existe filtro, mostramos todas los productos
    } else {
    filteredProducts  = filter(productsDataSet); // Aplicamos el filtro a los productos
    }
  // console.log("Productos filtrados   :", filteredProducts);
 }

  // Crea el filtro 
  function applyFilter(value) { 
    // console.log("Valor del filtro aplicado:", value);
    filter = createArrayFilter(value);
    // console.log("Filtro creado:", filter)
    filterProducts()
  }

  // Función para limpiar los filtros aplicados en el Grid
  function clearFilters() {
    // gridRef.exec("filter-rows", {});
    filter = null;
    filterProducts()
    filterId = 1; // Para poder eliminar valores de estos filtros
    // Esto es útil si no tienes un id o una clase para el input
    const inputElement = document.querySelector('input[placeholder="Buscar en todos los campos"]'); // Buscar campo de entrada del filtro
    if (inputElement) {
        inputElement.value = ''; // Limpiar el valor del input
    }
  }
  // End Filter External -------------------------------------------------------------------------------------------------

// Exportar Películas Filtradas --------------------------------------------------------------------------------------------------
function exportToExcel() {
    console.log("datos a exportar: ", filteredProducts);
    // Elimino campos que no quiero en el Excel
    const registrosLimpios = filteredProducts.map(({ unit_measure_id, date_last_buy_text,section_group_id, ...resto }) => resto); 

    const worksheet = XLSX.utils.json_to_sheet(registrosLimpios);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
    XLSX.writeFile(workbook, "productos-filtradas.xlsx");
}
// End Exportar Películas Filtradas

// Realizar un informe con PdfMake
async function handlePDF () {
  try {
    const res = await api.get(`/informe/${groupSelected.id}`);
    const datos = res.data.data;
    console.log("Datos del informe: ",datos);
    generarPDF(datos);                        // Elaboración del Informe de Lista de la Compra
    addToast({
      message:'Informe realizado correctamente' ,
      type: 'success',
      dismissible: true,
      timeout: 2000
    });
  } catch (err) {
    handleFatalError({err, cambiarVista});              // Manejo del error fatal
  } finally {
    // Nada
  }
}
// Fin del informe

</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><Fa icon={faCarrot} /> Productos del grupo: {groupSelected?.name}</h3>  

  <!-- Definición del Conjunto de Productos a Mostrar -->
  <div class="wx-filter-bar"> 
    <strong>Productos:</strong> 
    <Tabs value={dataSetId} options={dataSets} onchange={handleDataSetChange} />
  </div> <br/>
  <!-- Definición de los filtros Externos -->
  <div class="wx-filter-bar"> 
    <strong>Filtros:</strong> 
    <Tabs value={filterId} options={filterTabs} onchange={handleFilterChange} />
    <Locale words={{ ...filterEs, ...coreEs }}>
        {#if filterId === 1}
        <FilterBar
            fields={[
            {
                type: "all",
                by:  ["name", "section"],
                placeholder: "Buscar en todos los campos",
            },
            ]}
            onchange={({ value }) => applyFilter(value)}
        />
        {:else if filterId === 2}
        <FilterBar
            fields={[
            {
                type: "text",
                id: "section",
                /* label: "Tema",*/
                placeholder: "Buscar por Sección",
                options:  getOptions(products, "section"),
            },
            ]}
            onchange={({ value }) => applyFilter(value)}
        />
        {:else if filterId === 3}
        <FilterBar
            fields={[
            {
                type: "dynamic",
                label: "Campo:",
                by: fieldFilter ,
            },
            ]}
            onchange={({ value }) => applyFilter(value)}
        />
        {/if}

    </Locale>
  </div> 
  <!-- Fin de la definición de los filtros Externos-->

  <!-- Definición del DataGrid -->
  <div class="d-flex gap-2">
    <Button type="primary" onclick={clearFilters}>
        <Fa icon={faBroom} />  filtros </Button> 
    <Button type="primary"  
        onclick={() => {
          editorMode = "add";
          editorValues = {};
          showEditor = true;
        }}>
        <Fa icon={faPlusCircle} />  Producto </Button> 
    <Button type="primary"  
        onclick={() => { 
          columnHidden = !columnHidden; 
        }}> 
        <Fa icon={columnHidden ? faEye : faEyeSlash} /> Móvil  </Button>
    {#if !columnHidden}    
      <Button type="default" text="📝 Export Excel" onclick={exportToExcel} />
    {/if}
      <Button type="default" text="📋 PDF" onclick={handlePDF} />
      </div>
    {#if !columnHidden}    
      <div>Para el acceso a las acciones, botón derecho en el Grid</div>
    {:else}    
      <div>Para el acceso a las acciones,mantener selección del registro</div>
    {/if}

</div>
{#if loading}
  <p class="text-center">Cargando películas...</p>
{:else}
  <ContextMenu
    options={contextOptions}
    onclick={handleContext}
    at="point"
    resolver={resolver}
    api={gridRef}
  >
    <Locale words={{ ...gridEs, ...coreEs }}>
      <div style="height: 360px; max-width: 1200px;">
      <Grid
        bind:this={gridRef}
        data={filteredProducts}
        {columns}
        {columnStyle}
        pager={false}
        onselectrow={updateSelected}
        autoRowHeight={!columnHidden}
      />
      </div>
    </Locale>
  </ContextMenu>
{/if}
 <!--Fin Definición del DataGrid -->

 <!-- Definición de Edición   -->
{#if showEditor}
  <SideArea>
    <ProductosForm {editorMode} {editorValues} {products} {sections} {measures} {handleSave} {closeEdit} />
  </SideArea>

{/if}
<!-- Fin Definición de Edición -->
{#if showDeleteConfirm}
  <ModalArea>
    <div class="modal-content">
      <h3>¿Eliminar este Producto?</h3>
      <p>Nombre: <b>{productToDelete?.name}</b></p>
      <div class="actions">
        <Button type="danger" onclick={proceedDelete}>Confirmar</Button>
        <Button onclick={cancelDelete} color="secondary">Cancelar</Button>
      </div>
    </div>
  </ModalArea>
{/if}

<style>
   @media (max-width: 768px) { /* Ajustes para pantallas pequeñas  en los campos de Filtros */ 
    :global(.wx-filter-bar) 
    { 
        width: auto !important; 
    }
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

  :global(.wx-sidearea) {
    width: 300px;
  }
</style>

