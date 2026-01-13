'use client'

import * as React from "react"
import Link from "next/link"
import { Menu, X } from "lucide-react"

import { cn } from "@/lib/utils"
// import { Icons } from "@/components/icons" // We can use CriIcon or just text for now
import { Button, buttonVariants } from "@/components/ui/button"
import {
    Sheet,
    SheetContent,
    SheetTrigger,
    SheetHeader,
    SheetTitle,
} from "@/components/ui/sheet"
import { UserNav } from "@/components/layout/user-nav"

interface NavItem {
    title: string
    href: string
    disabled?: boolean
}

const items: NavItem[] = [
    {
        title: "Home",
        href: "/",
    },
    {
        title: "Anagrafica",
        href: "/contacts",
    },
]

export function Navbar() {
    const [isOpen, setIsOpen] = React.useState(false)

    return (
        <header className="sticky top-0 z-40 w-full border-b bg-background">
            <div className="container flex h-16 items-center space-x-4 sm:justify-between sm:space-x-0">
                <div className="flex gap-6 md:gap-10">
                    <Link href="/" className="flex items-center space-x-2">
                        <span className="inline-block font-bold text-red-600 text-xl">CRI Venezia</span>
                    </Link>
                    <nav className="hidden md:flex gap-6">
                        {items?.map(
                            (item, index) =>
                                item.href && (
                                    <Link
                                        key={index}
                                        href={item.href}
                                        className={cn(
                                            "flex items-center text-sm font-medium text-muted-foreground",
                                            item.disabled && "cursor-not-allowed opacity-80",
                                            "hover:text-primary transition-colors"
                                        )}
                                    >
                                        {item.title}
                                    </Link>
                                )
                        )}
                    </nav>
                </div>
                <div className="flex flex-1 items-center justify-end space-x-4">
                    <div className="flex items-center space-x-4">
                        <UserNav />
                        <Sheet open={isOpen} onOpenChange={setIsOpen}>
                            <SheetTrigger asChild>
                                <Button variant="ghost" className="md:hidden p-0 w-10 h-10">
                                    <Menu className="h-6 w-6" />
                                    <span className="sr-only">Toggle Menu</span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" className="pr-0">
                                <SheetHeader>
                                    <SheetTitle className="sr-only">Menu di Navigazione</SheetTitle>
                                </SheetHeader>
                                <div className="px-7">
                                    <Link href="/" className="flex items-center" onClick={() => setIsOpen(false)}>
                                        <span className="font-bold text-red-600">CRI Venezia</span>
                                    </Link>
                                </div>
                                <div className="flex flex-col gap-4 mt-8 px-7">
                                    {items.map((item, index) => (
                                        <Link
                                            key={index}
                                            href={item.href}
                                            className="text-lg font-medium hover:text-red-600"
                                            onClick={() => setIsOpen(false)}
                                        >
                                            {item.title}
                                        </Link>
                                    ))}
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </div>
        </header>
    )
}
