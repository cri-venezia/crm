import { z } from "zod"

export const contactFormSchema = z.object({
    first_name: z.string().min(2, {
        message: "Nome richiesto (min 2 caratteri).",
    }),
    last_name: z.string().min(2, {
        message: "Cognome richiesto (min 2 caratteri).",
    }),
    email: z.string().email({
        message: "Email non valida.",
    }).optional().or(z.literal('')),
    phone: z.string().optional(),
    type: z.enum(["volontario", "sostenitore", "donatore"], {
        required_error: "Seleziona un tipo.",
    }),
    tags: z.string().optional(), // We'll handle CSV splitting in the component
    notes: z.string().optional(),
})

export type ContactFormValues = z.infer<typeof contactFormSchema>
