import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Search, Store, CheckCircle, XCircle, AlertCircle } from 'lucide-react';
import { useState } from 'react';

interface MerchantsPageProps {
    merchants: {
        data: Array<{
            id: number;
            business_name: string;
            email: string;
            status: string;
            city: string;
            state: string;
            created_at: string;
            user: { name: string; email: string };
        }>;
        meta: {
            current_page: number;
            last_page: number;
            total: number;
        };
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    statuses: string[];
    filters: {
        status?: string;
        search?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Merchants', href: '/admin/merchants' },
];

export default function AdminMerchants({ merchants, statuses, filters }: MerchantsPageProps) {
    const [selectedMerchant, setSelectedMerchant] = useState<typeof merchants.data[0] | null>(null);
    const [newStatus, setNewStatus] = useState('');

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            pending: 'bg-yellow-100 text-yellow-800',
            approved: 'bg-green-100 text-green-800',
            suspended: 'bg-red-100 text-red-800',
            rejected: 'bg-gray-100 text-gray-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'approved':
                return <CheckCircle className="h-4 w-4" />;
            case 'suspended':
            case 'rejected':
                return <XCircle className="h-4 w-4" />;
            default:
                return <AlertCircle className="h-4 w-4" />;
        }
    };

    const handleSearch = (value: string) => {
        router.get('/admin/merchants', { ...filters, search: value }, { preserveState: true });
    };

    const handleStatusFilter = (value: string) => {
        router.get('/admin/merchants', { ...filters, status: value === 'all' ? '' : value }, { preserveState: true });
    };

    const handleUpdateStatus = () => {
        if (selectedMerchant && newStatus) {
            router.put(`/admin/merchants/${selectedMerchant.id}/status`, {
                status: newStatus,
            }, {
                onSuccess: () => {
                    setSelectedMerchant(null);
                    setNewStatus('');
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Merchants" />
            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Manage Merchants</h1>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex gap-4 items-center">
                            <div className="relative flex-1 max-w-sm">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search merchants..."
                                    defaultValue={filters.search}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <Select value={filters.status || 'all'} onValueChange={handleStatusFilter}>
                                <SelectTrigger className="w-40">
                                    <SelectValue placeholder="Filter by status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    {statuses.map((status) => (
                                        <SelectItem key={status} value={status}>
                                            {status.charAt(0).toUpperCase() + status.slice(1)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Merchants Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Merchants ({merchants.meta.total})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Business Name</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Joined</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {merchants.data.map((merchant) => (
                                    <TableRow key={merchant.id}>
                                        <TableCell className="font-medium">
                                            <div className="flex items-center gap-2">
                                                <Store className="h-4 w-4 text-muted-foreground" />
                                                {merchant.business_name}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div>{merchant.user?.name}</div>
                                            <div className="text-sm text-muted-foreground">{merchant.email}</div>
                                        </TableCell>
                                        <TableCell>{merchant.city}, {merchant.state}</TableCell>
                                        <TableCell>
                                            <Badge className={getStatusColor(merchant.status)}>
                                                {getStatusIcon(merchant.status)}
                                                <span className="ml-1">{merchant.status}</span>
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{new Date(merchant.created_at).toLocaleDateString()}</TableCell>
                                        <TableCell>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => setSelectedMerchant(merchant)}
                                                    >
                                                        Update Status
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Update Merchant Status</DialogTitle>
                                                        <DialogDescription>
                                                            Change the status for {merchant.business_name}
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <div className="py-4">
                                                        <Select value={newStatus} onValueChange={setNewStatus}>
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Select new status" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {statuses.map((status) => (
                                                                    <SelectItem key={status} value={status}>
                                                                        {status.charAt(0).toUpperCase() + status.slice(1)}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                    <DialogFooter>
                                                        <Button variant="outline" onClick={() => setSelectedMerchant(null)}>
                                                            Cancel
                                                        </Button>
                                                        <Button onClick={handleUpdateStatus} disabled={!newStatus}>
                                                            Update Status
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        {merchants.meta.last_page > 1 && (
                            <div className="mt-4 flex justify-center gap-2">
                                {merchants.links.map((link, index) => (
                                    link.url ? (
                                        <Button
                                            key={index}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            onClick={() => router.get(link.url!)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ) : (
                                        <span
                                            key={index}
                                            className="px-3 py-2 text-muted-foreground"
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    )
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
