import pdfMake from "pdfmake/build/pdfmake";
import "pdfmake/build/vfs_fonts";


export function generarPDF(datos) {

    // Formateador de números
    let decimal = new Intl.NumberFormat('es-ES', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 3,
    useGrouping: true
    });

    // ------------------------------------------------------------
    // CONSTRUCCIÓN MANUAL DEL CONTENT (SIN SPREAD)
    // ------------------------------------------------------------
    let contenido = [];

    // Cabecera general
    contenido.push(
    {
        text: 'Lista de la Compra',
        style: 'header',
        alignment: 'center',
        margin: [0, 0, 0, 10]
    },
    {
    layout: 'noBorders',
    table: {
        widths: ['*', 'auto'],
        body: [
        [
            { text: `Grupo: ${datos.Cabecera.grupo}`, alignment: 'left', style: 'group' },
            { text: `Fecha: ${datos.Cabecera.fecha}`, alignment: 'right' }
        ]
        ]
    },
    margin: [0, 0, 0, 20]
    },

    );

    // Secciones
    datos.lineas.forEach(seccion => {

    let tabla = {
        table: {
        headerRows: 2,
        widths: ['*', 'auto', 'auto', 90],
        body: []
        },
        layout: 'lightHorizontalLines',
        margin: [0, 0, 0, 20]
    };

    // Fila 1: nombre de la sección
    tabla.table.body.push([
        {
        text: `Sección: ${seccion.seccion}`,
        style: 'sectionHeader',
        colSpan: 4,
        alignment: 'left'
        },
        {}, {}, {}
    ]);

    // Fila 2: cabecera de columnas
    tabla.table.body.push([
        { text: 'Artículo', style: 'tableHeader' },
        { text: 'Cantidad', style: 'tableHeader', alignment: 'center' },
        { text: 'Unidad', style: 'tableHeader', alignment: 'left' },
        { text: 'Foto', style: 'tableHeader', alignment: 'center' }
    ]);

    // Filas de artículos
    seccion.articulos.forEach(a => {
        tabla.table.body.push([
        a.name,
        { text: decimal.format(a.amount), alignment: 'right' },
        a.title,
        a.photo
                ? { image: a.photo, width: 75 }
                : ''
        ]);
    });

    contenido.push(tabla);
    });

  // Documento PDFMake
  let dd = {
    pageSize: 'A4',
    pageOrientation: 'portrait',
    pageMargins: [50, 30, 20, 40],

    footer: function (currentPage, pageCount) {
      return {
        text: `Página: ${currentPage} de ${pageCount}`,
        alignment: 'left',
        margin: [30, 10, 0, 0]
      };
    },

    content: contenido,

    styles: {
      header: { fontSize: 18, bold: true, alignment: 'center' },
      group: { fontSize: 12, bold: true},
      sectionHeader: { fontSize: 14, bold: true, fillColor: '#eeeeee', margin: [0, 5, 0, 5] },
      tableHeader: { bold: true, fontSize: 12, fillColor: '#dddddd' }
    }
  };

  // Nombre del archivo con fecha y hora
  const ahora = new Date();
  const nombre = `informe_${ahora.getFullYear()}-${String(ahora.getMonth()+1).padStart(2,'0')}-${String(ahora.getDate()).padStart(2,'0')}_${String(ahora.getHours()).padStart(2,'0')}-${String(ahora.getMinutes()).padStart(2,'0')}-${String(ahora.getSeconds()).padStart(2,'0')}.pdf`;

  pdfMake.createPdf(dd).download(nombre);
}
