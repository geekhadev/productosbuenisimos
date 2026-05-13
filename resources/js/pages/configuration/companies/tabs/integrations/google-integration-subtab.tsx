import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { GoogleCalendarSubtab } from '@/pages/configuration/companies/tabs/integrations/google-calendar-subtab';
import { GoogleGmailSubtab } from '@/pages/configuration/companies/tabs/integrations/google-gmail-subtab';

export function GoogleIntegrationSubtab() {
    return (
        <div className="w-full space-y-6">
            <div>
                <h3 className="text-lg font-medium">Google</h3>
                <p className="text-muted-foreground text-sm">
                    Conexiones con servicios de Google (en preparación).
                </p>
            </div>

            <Tabs defaultValue="gmail" className="w-full gap-6">
                <TabsList className="grid h-auto min-h-9 w-full max-w-lg grid-cols-2 gap-1 p-1">
                    <TabsTrigger value="gmail" className="h-9 w-full">
                        Gmail
                    </TabsTrigger>
                    <TabsTrigger value="calendar" className="h-9 w-full">
                        Google Calendar
                    </TabsTrigger>
                </TabsList>
                <TabsContent value="gmail" className="mt-6 outline-none">
                    <GoogleGmailSubtab />
                </TabsContent>
                <TabsContent value="calendar" className="mt-6 outline-none">
                    <GoogleCalendarSubtab />
                </TabsContent>
            </Tabs>
        </div>
    );
}
