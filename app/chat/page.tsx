import { ErikaChat } from "@/components/chat/erika-chat";

export default function ChatPage() {
    return (
        <main className="container mx-auto p-4 md:p-8 h-[calc(100vh-4rem)] flex flex-col">
            <div className="flex flex-col space-y-4 max-w-4xl mx-auto w-full h-full">
                <div className="flex items-center space-x-2">
                    <h1 className="text-3xl font-bold tracking-tight text-red-600">Erika</h1>
                    <span className="text-muted-foreground">- Assistente Operativo</span>
                </div>
                <div className="flex-1 overflow-hidden">
                    <ErikaChat />
                </div>
            </div>
        </main>
    );
}
