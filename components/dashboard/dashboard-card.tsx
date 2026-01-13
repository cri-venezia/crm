import Link from "next/link";
import { LucideIcon } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import { CriIcon, IconName } from "@/components/ui/cri-icon";

interface DashboardCardProps {
    title: string;
    description: string;
    icon?: LucideIcon;
    iconName?: IconName | string;
    href: string;
    className?: string;
    variant?: "default" | "red" | "pink" | "blue" | "green";
}

export function DashboardCard({
    title,
    description,
    icon: Icon,
    iconName,
    href,
    className,
    variant = "default",
}: DashboardCardProps) {
    const variantStyles = {
        default: {
            border: "hover:border-primary/50",
            icon: "group-hover:text-primary",
        },
        red: {
            border: "hover:border-red-500/50 dark:hover:border-red-400/50",
            icon: "text-red-500 dark:text-red-400",
        },
        pink: {
            border: "hover:border-pink-500/50 dark:hover:border-pink-400/50",
            icon: "text-pink-500 dark:text-pink-400",
        },
        blue: {
            border: "hover:border-blue-500/50 dark:hover:border-blue-400/50",
            icon: "text-blue-500 dark:text-blue-400",
            // bg: "group-hover:bg-blue-50 dark:group-hover:bg-blue-900/10" // Optional nice touch?
        },
        green: {
            border: "hover:border-green-500/50 dark:hover:border-green-400/50",
            icon: "text-green-500 dark:text-green-400",
        },
    };

    const styles = variantStyles[variant];

    return (
        <Link href={href} className={cn("block group", className)}>
            <Card className={cn(
                "h-full transition-all duration-300 hover:shadow-md border-2",
                "hover:-translate-y-1", // Subtle lift effect
                styles.border
            )}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-xl font-bold">{title}</CardTitle>
                    {iconName ? (
                        <CriIcon name={iconName} className={cn("h-6 w-6 transition-colors", styles.icon)} />
                    ) : (
                        Icon && <Icon className={cn("h-6 w-6 transition-colors", styles.icon)} />
                    )}
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </CardContent>
            </Card>
        </Link>
    );
}
