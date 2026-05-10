<script>
  import { Text } from "wx-svelte-core";
  import Fa from 'svelte-fa';
  import { faEyeSlash, faEye }
        from '@fortawesome/free-solid-svg-icons';

  // 👈 PATRÓN OFICIAL SVAR
  let { value, onchange, label, placeholder, required, maxLength, ...restProps } = $props();
  
  let show = $state(false);

  function toggleShow() {
    show = !show;
  }
</script>

<div class="wx-password-wrapper">
  <!-- 👈 Pasa TODO directamente al Text -->
  <Text 
    {value}
    {onchange}
    {label}
    {placeholder}
    {required}
    {maxLength}
    type={show ? "text" : "password"}
    {...restProps}
  />
  <button
    type="button"
    class="toggle"
    onclick={toggleShow}
  >
  <Fa icon={show ? faEye : faEyeSlash} />
  
  </button>
</div>

<!-- Estilos iguales -->
<style>
  .wx-password-wrapper {
    position: relative;
    width: 100%;
  }
  .toggle {
    position: absolute;
    right: -18px;
    top: -6px;
    color: darkslategray;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    z-index: 10;
  }
</style>