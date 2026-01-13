'use client'

import { useState } from "react"
import { Contact, deleteContact } from "@/app/contacts/actions"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Button } from "@/components/ui/button"
import { MoreHorizontal, Pencil, Trash2, Mail, Phone } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { CriIcon } from "@/components/ui/cri-icon"
import { ContactForm } from "./contact-form"

interface ContactsListProps {
    contacts: Contact[]
}

export function ContactsList({ contacts }: ContactsListProps) {
    const [editingContact, setEditingContact] = useState<Contact | null>(null)

    const handleDelete = async (id: string) => {
        if (confirm("Sei sicuro di voler eliminare questo contatto?")) {
            await deleteContact(id)
        }
    }

    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'volontario': return 'user-nurse';
            case 'donatore': return 'heart-circle-plus';
            case 'sostenitore': return 'star-of-life';
            default: return 'id-card-clip';
        }
    }

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'volontario': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'donatore': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            case 'sostenitore': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    return (
        <>
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-[200px]">Nome</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Contatti</TableHead>
                            <TableHead>Tags</TableHead>
                            <TableHead className="text-right">Azioni</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {contacts.map((contact) => (
                            <TableRow key={contact.id}>
                                <TableCell className="font-medium">
                                    <div className="flex flex-col">
                                        <span className="text-base font-semibold">{contact.first_name} {contact.last_name}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" className={`gap-1 pr-3 py-1 ${getTypeColor(contact.type)}`}>
                                        <CriIcon name={getTypeIcon(contact.type)} size={16} />
                                        <span className="capitalize">{contact.type}</span>
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="flex flex-col gap-1 text-sm text-muted-foreground">
                                        {contact.email && (
                                            <div className="flex items-center gap-1">
                                                <Mail className="w-3 h-3" /> {contact.email}
                                            </div>
                                        )}
                                        {contact.phone && (
                                            <div className="flex items-center gap-1">
                                                <Phone className="w-3 h-3" /> {contact.phone}
                                            </div>
                                        )}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="flex flex-wrap gap-1">
                                        {contact.tags.map(tag => (
                                            <span key={tag} className="inline-flex items-center rounded-sm bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                                {tag}
                                            </span>
                                        ))}
                                    </div>
                                </TableCell>
                                <TableCell className="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" className="h-8 w-8 p-0">
                                                <span className="sr-only">Menu</span>
                                                <MoreHorizontal className="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Azioni</DropdownMenuLabel>
                                            <DropdownMenuItem onClick={() => setEditingContact(contact)}>
                                                <Pencil className="mr-2 h-4 w-4" /> Modifica
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem onClick={() => handleDelete(contact.id)} className="text-red-600">
                                                <Trash2 className="mr-2 h-4 w-4" /> Elimina
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        ))}
                        {contacts.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={5} className="h-24 text-center">
                                    Nessun contatto trovato.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {editingContact && (
                <ContactForm
                    open={!!editingContact}
                    onOpenChange={(open) => !open && setEditingContact(null)}
                    contact={editingContact}
                />
            )}
        </>
    )
}
