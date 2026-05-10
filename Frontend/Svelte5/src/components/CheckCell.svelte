<script>
    import { Fa } from "svelte-fa";
    import { faCheck } from "@fortawesome/free-solid-svg-icons";

    let { row, column } = $props();

    // Valor crudo desde el grid
    const raw = $derived(row?.[column?.id]);

    // Lo normalizamos a boolean: true si 1 (numérico o string)
    const checked = $derived(raw == 1);
</script>

<div class="check-cell {checked ? 'checked' : 'unchecked'}">
    {#if checked}
        <Fa icon={faCheck} />
    {/if}
</div>

<style>
    .check-cell {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;

        border: 1px solid #999;
        border-radius: 3px;
        background: #fff;

        cursor: default;
        box-sizing: border-box;
        padding: 0;
    }

    .check-cell :global(svg) {
        width: 12px;
        height: 12px;
        color: #0a0;
    }

    .unchecked {
        background: #fff;
    }

    .checked {
        background: #eaffea;
        border-color: #0a0;
    }
</style>
