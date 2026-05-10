<script>
    import { Text, Button } from "wx-svelte-core";

    let { value, onchange, readonly = false, ...restProps } = $props();

    let preview = $state("");
    let fileName = $state("");
    let fileInput;

    const MAX_WIDTH = 250;

    // Detectar cambios externos
    $effect(() => {
        if (!value || typeof value !== "object" || value.base64 === null) {
            preview = "";
            fileName = "";
            return;
        }

        if (value.filename !== "") {
            preview = `data:${value.mimetype};base64,${value.base64}`;
            fileName = value.filename;
        } else {
            preview = "";
            fileName = "";
        }
    });

    function triggerFileSelect() {
        if (!readonly) fileInput.click();
    }

    async function handleSelect(e) {
        if (readonly) return;

        const file = e.target.files?.[0];
        if (!file) {
            onchange({ value: null });
            preview = "";
            fileName = "";
            return;
        }

        fileName = file.name;

        const img = await cargarImagen(file);

        let canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;

        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);

        if (canvas.width > MAX_WIDTH || canvas.height > MAX_WIDTH) {
            const scale =
                canvas.width > canvas.height
                    ? MAX_WIDTH / canvas.width
                    : MAX_WIDTH / canvas.height;

            const resizedCanvas = document.createElement("canvas");
            resizedCanvas.width = canvas.width * scale;
            resizedCanvas.height = canvas.height * scale;

            const resizedCtx = resizedCanvas.getContext("2d");
            resizedCtx.drawImage(
                canvas,
                0,
                0,
                resizedCanvas.width,
                resizedCanvas.height
            );

            canvas = resizedCanvas;
        }

        canvas.toBlob(async (blob) => {
            if (!blob) return;

            const compressedFile = new File([blob], file.name, { type: file.type });

            preview = URL.createObjectURL(blob);

            const base64 = await blobToBase64(blob);

            const obj = {
                filename: compressedFile.name,
                mimetype: compressedFile.type,
                size: compressedFile.size,
                base64: base64,
            };

            onchange({ value: obj });
        }, file.type);
    }

    function cargarImagen(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }

    function blobToBase64(blob) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => {
                const base64 = reader.result.split(",")[1];
                resolve(base64);
            };
            reader.readAsDataURL(blob);
        });
    }

    function clearFile() {
        if (readonly) return;

        fileInput.value = "";
        preview = "";
        fileName = "";
        onchange({ value: null });
    }
</script>

<div class="wx-field wx-file-field">

    <!-- Solo mostrar el botón si NO estamos en readonly -->
    {#if !readonly}
        <Button type="secondary" text="Seleccionar fichero" onclick={triggerFileSelect} />
    {/if}

    <!-- Input oculto -->
    <input
        type="file"
        accept="image/png, image/jpeg"
        bind:this={fileInput}
        onchange={handleSelect}
        disabled={readonly}
        hidden
    />

    {#if preview}
        <div class="wx-file-preview-inline">
            <img src={preview} class="wx-file-thumbnail" alt="preview" />

            <div>
                <!-- {fileName} -->
                <!-- <br />   -->

                <!-- Botón Quitar solo si NO estamos en readonly -->
                {#if !readonly}
                    <Button type="danger" text="Quitar" onclick={clearFile} />
                {/if}
            </div>
        </div>
    {/if}
</div>

<style>
    .wx-file-preview-inline {
        display: inline-block;
        flex-direction: row;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
    }
    .wx-file-thumbnail {
        max-width: 200px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }
</style>
