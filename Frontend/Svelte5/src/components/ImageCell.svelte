<script>
    let { row, column } = $props();

    const raw = $derived(row?.[column?.id]);

    const src = $derived(
        raw && typeof raw === "object" && raw.base64
            ? `data:${raw.mimetype};base64,${raw.base64}`
            : null
    );
</script>

<div class="image-cell">
    {#if src}
        <img src={src} alt="img" class="thumb" />
    {:else}
        <div class="placeholder">—</div>
    {/if}
</div>

<style>
    .image-cell {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;

        /* Eliminar padding para evitar desplazamientos */
        padding: 0;

        /* Evitar que SVAR fuerce overflow extraño */
        overflow: hidden;
        box-sizing: border-box;
    }

    .thumb {
        width: 100%;
        height: 100%;
        object-fit: contain;

        /* Eliminar bordes que empujan la imagen */
        border: none;
        margin: 0;
        padding: 0;
    }

    .placeholder {
        font-size: 12px;
        color: #aaa;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
</style>
