<script>
  import { getContext } from "svelte";
  import { Field } from "@svar-ui/svelte-core";

  const _ = getContext("wx-i18n").getGroup("editor");

  let { value, options, label, CustomReadonly } = $props();

// funciín de tranformación de nueva línea en memo
function nl2br(text) {
    if (!text) return "";
    return text.replace(/\n/g, "<br />");
}

let text = $derived.by(() => {
    // console.log("CustomReadonly:", CustomReadonly, "Value:", value);
    let text = value;
    switch (CustomReadonly) {
        case "Date":
            if (value) {
                const date = new Date(value);
                text = date.toLocaleDateString();
            } else {
                text = "";
            }
            break;
        case "Datetime":
            if (value) {
                const date = new Date(value);
                text = date.toLocaleString();
            } else {
                text = "";
            }
            // console.log("Formatted Datetime:", text);
            break;
        case "Number":
            text = Number(value).toLocaleString();
            break;
        case "Currency":
            text = Number(value).toLocaleString(undefined, { style: 'currency', currency: 'USD' });
            break;
        case "Percentage":
            text = Number(value).toLocaleString(undefined, { style: 'percent' });
            break;
        case "Memo":
            text = nl2br(value);
            break;
        case "Boolean":
            text = value ? _("Yes") : _("No");
            break;
        case "Select":
            // console.log("Options:", options, "Value:", value);
            if ( value === null || value === undefined ) { break;}
            const option = options.find(opt => opt.id === value);
            text = option.label || value;
            break;
        default:
            break;
    }
    return text;
});
  
</script>

<div>
 {#if CustomReadonly === "Memo"}
    <Field {label}>{@html text}</Field>
{:else}
    <Field {label}>{text}</Field>
  {/if}
</div>