import { cn } from "@/lib/utils"
import { HTMLAttributes } from "react"

function Skeleton({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn("animate-pulse rounded-md bg-white/10 dark:bg-muted", className)}
            {...props}
        />
    )
}

export { Skeleton }
