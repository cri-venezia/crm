'use client'

import { useState } from "react"
import { ContactForm } from "@/components/contacts/contact-form"
import { Button } from "@/components/ui/button"
import { UserPlus } from "lucide-react"

export function ContactFormWrapper() {
    const [open, setOpen] = useState(false)

    return (
        <>
            <Button onClick={() => setOpen(true)} className="bg-red-600 hover:bg-red-700 gap-2">
                <UserPlus className="w-4 h-4" />
                Nuovo Contatto
            </Button>
            <ContactForm open={open} onOpenChange={setOpen} />
        </>
    )
}
