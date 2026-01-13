import { Skeleton } from "@/components/ui/skeleton";

export default function Loading() {
    return (
        <main className="flex min-h-screen flex-col p-8 bg-gray-50 dark:bg-gray-900">
            <div className="flex flex-col gap-8 max-w-7xl mx-auto w-full">
                <div className="flex items-center gap-4">
                    <Skeleton className="h-12 w-12 rounded-full" />
                    <div className="space-y-2">
                        <Skeleton className="h-8 w-48" />
                        <Skeleton className="h-4 w-64" />
                    </div>
                </div>
                <div className="w-full max-w-2xl mx-auto space-y-6">
                    <Skeleton className="h-[500px] w-full rounded-xl" />
                </div>
            </div>
        </main>
    );
}
