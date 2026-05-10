<script>
	import { DatePicker, TimePicker } from "@svar-ui/svelte-core";

	let { value, time, format, onchange, ...restProps } = $props();

    // Normalización reactiva del valor en Svelte 5
    $effect(() => {
        if (value == null || value === "") {
            value = new Date();
        }
    });

	function handleDateChange(ev) {
		const current = new Date(ev.value);
		current.setHours(value.getHours());
		current.setMinutes(value.getMinutes());

        current.setSeconds(0);              // Añadido para dejar valor "00" en segundos en caso de modificación
        current.setMilliseconds(0);         // opcional, pero recomendable
        
		onchange && onchange({ value: current });
	}
    function handleTimeChange(ev) {
        const current = new Date(ev.value);

        current.setSeconds(0);
        current.setMilliseconds(0);

        onchange && onchange({ value: current });
    }

</script>

<div class="date-time-controll">
	<DatePicker
		{...restProps}
		{value}
		onchange={handleDateChange}
		{format}
		buttons={["today"]}
		clear={false}
	/>
	{#if time}
		<TimePicker {value} onchange={handleTimeChange}  />
	{/if}
</div>

<style>
	.date-time-controll {
		display: flex;
		gap: 12px;
	}
</style>