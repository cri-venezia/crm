import { Skeleton } from "@/components/ui/skeleton";

export default function Loading() {
    return (
        <main className="flex min-h-screen flex-col p-8 bg-gray-50 dark:bg-gray-900">
            <div className="flex flex-col gap-8 max-w-7xl mx-auto w-full">
                <div className="flex items-end justify-between">
                    <div className="flex items-center gap-4">
                        <Skeleton className="h-12 w-12 rounded-full" />
                        <div className="space-y-2">
                            <Skeleton className="h-8 w-48" />
                            <Skeleton className="h-4 w-64" />
                        </div>
                    </div>
                    <Skeleton className="h-10 w-32" />
                </div>

                <div className="flex items-center gap-4">
                    <Skeleton className="h-10 w-full max-w-sm" />
                </div>

                <div className="bg-white dark:bg-slate-950 rounded-lg shadow border p-4 space-y-4">
                    {Array.from({ length: 5 }).map((_, i) => (
                        <div key={i} className="flex items-center space-x-4">
                            <Skeleton className="h-12 w-12 rounded-full" />
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-[250px]" />
                                <Skeleton className="h-4 w-[200px]" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </main>
    );
}
