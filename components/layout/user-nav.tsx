'use client'

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Button } from "@/components/ui/button"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { useEffect, useState } from "react"
import { User, LogOut } from "lucide-react"
import Link from "next/link"

export function UserNav() {
    const [user, setUser] = useState<{ email: string | undefined, initials: string }>({ email: undefined, initials: 'U' })

    useEffect(() => {
        const getUser = async () => {
            const { createSupabaseClient } = await import('@/lib/supabase');
            const supabase = createSupabaseClient();
            const { data: { user } } = await supabase.auth.getUser();
            if (user) {
                const email = user.email;
                let initials = 'U';
                if (user.user_metadata?.first_name && user.user_metadata?.last_name) {
                    initials = `${user.user_metadata.first_name[0]}${user.user_metadata.last_name[0]}`.toUpperCase();
                } else if (email) {
                    initials = email.substring(0, 2).toUpperCase();
                }

                setUser({ email, initials })
            }
        };
        getUser();
    }, [])

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                    <Avatar className="h-8 w-8">
                        <AvatarImage src="" alt="@cri" />
                        <AvatarFallback className="bg-red-100 text-red-600">{user.initials}</AvatarFallback>
                    </Avatar>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="end" forceMount>
                <DropdownMenuLabel className="font-normal">
                    <div className="flex flex-col space-y-1">
                        <p className="text-sm font-medium leading-none">Account</p>
                        <p className="text-xs leading-none text-muted-foreground">
                            {user.email || '...'}
                        </p>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href="/profile" className="cursor-pointer w-full flex items-center">
                        <User className="mr-2 h-4 w-4" />
                        <span>Profilo</span>
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <form action="/auth/signout" method="post" className="w-full">
                        <button className="w-full flex items-center text-red-600">
                            <LogOut className="mr-2 h-4 w-4" />
                            <span>Esci</span>
                        </button>
                    </form>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    )
}
