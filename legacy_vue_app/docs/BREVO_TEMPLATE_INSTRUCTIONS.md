# Guida Template Brevo (Dynamic List)

Per visualizzare la lista degli articoli inviati dal CRM, devi usare il linguaggio di templating di Brevo (simile a Django/Jinja).

Ecco come strutturare il blocco ripetitivo nel tuo Design Tool su Brevo.

## Sintassi Base

Devi racchiudere il blocco HTML che vuoi ripetere tra i tag `{% for ... %}` e `{% endfor %}`.

### Esempio di Codice (Blocco HTML)

Trascina un blocco "HTML" nel tuo editor Brevo e incolla questo codice per testare:

```html
<!-- Inizio Loop Articoli -->
{% for article in params.articles %}
  <div style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
    
    <!-- Immagine (se presente) -->
    {% if article.imageUrl %}
      <img src="{{ article.imageUrl }}" alt="{{ article.title }}" style="width: 100%; max-width: 600px; height: auto; border-radius: 8px; margin-bottom: 15px;" />
    {% endif %}

    <!-- Titolo con Link -->
    <h2 style="font-family: Arial, sans-serif; color: #cc0000; margin-top: 0;">
      <a href="{{ article.link }}" style="color: #cc0000; text-decoration: none;">
        {{ article.title }}
      </a>
    </h2>

    <!-- Corpo del testo -->
    <p style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
      {{ article.body }}
    </p>

    <!-- Bottone Leggi di più -->
    {% if article.link %}
      <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td bgcolor="#cc0000" style="border-radius: 4px; padding: 10px 20px;">
            <a href="{{ article.link }}" style="color: #ffffff; font-family: Arial, sans-serif; text-decoration: none; font-weight: bold; display: inline-block;">
              Leggi tutto &rarr;
            </a>
          </td>
        </tr>
      </table>
    {% endif %}

  </div>
{% endfor %}
<!-- Fine Loop -->
```

## Variabili Disponibili

| Variabile | Descrizione |
| :--- | :--- |
| `{{ article.title }}` | Titolo dell'articolo |
| `{{ article.body }}` | Testo breve dell'articolo |
| `{{ article.link }}` | Link alla news originale (es. sito web) |
| `{{ article.imageUrl }}` | URL dell'immagine caricata su Supabase |

## Note Importanti
1. **Dynamic List**: Se usi l'editor visuale drag&drop di Brevo, puoi attivare la funzione "Dynamic List" (Lista Dinamica) su un blocco specifico.
   - **Variabile per la lista**: `params.articles`
   - **Variabile per l'elemento**: `article`
2. **Immagini**: Assicurati che il blocco immagine usi `{{ article.imageUrl }}` come sorgente URL.
