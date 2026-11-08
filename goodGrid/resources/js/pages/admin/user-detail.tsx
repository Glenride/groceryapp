import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { useState } from 'react';
import { ArrowLeft, Mail, Phone, Calendar, Shield } from 'lucide-react';

interface UserDetailProps {
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
        phone?: string;
        created_at: string;
        updated_at: string;
        orders?: Array<{
            id: number;
            order_number: string;
            status: string;
            total: number;
            created_at: string;
        }>;
        shopper?: {
            id: number;
            status: string;
            rating: number;
            total_batches: number;
        } | null;
        merchant?: {
            id: number;
            business_name: string;
            status: string;
        } | null;
    };
}

export default function UserDetail({ user }: UserDetailProps) {
    const [isEditing, setIsEditing] = useState(false);
    const [formData, setFormData] = useState({
        name: user.name,
        email: user.email,
        role: user.role,
        is_active: user.is_active,
    });

    const getRoleColor = (role: string) => {
        const colors: Record<string, string> = {
            customer: 'bg-blue-100 text-blue-800',
            shopper: 'bg-green-100 text-green-800',
            merchant: 'bg-purple-100 text-purple-800',
            admin: 'bg-red-100 text-red-800',
        };
        return colors[role] || 'bg-gray-100 text-gray-800';
    };

    const getStatusColor = (status: boolean) => {
        return status
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.put(`/admin/users/${user.id}`, formData, {
            onSuccess: () => setIsEditing(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User: ${user.name}`} />
            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/admin/users">
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <h1 className="text-3xl font-bold">{user.name}</h1>
                    </div>
                    <Button onClick={() => setIsEditing(!isEditing)}>
                        {isEditing ? 'Cancel' : 'Edit User'}
                    </Button>
                </div>

                {isEditing ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Edit User</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium mb-1">Name</label>
                                    <Input
                                        value={formData.name}
                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium mb-1">Email</label>
                                    <Input
                                        type="email"
                                        value={formData.email}
                                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium mb-1">Role</label>
                                    <Select
                                        value={formData.role}
                                        onValueChange={(value) => setFormData({ ...formData, role: value })}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="customer">Customer</SelectItem>
                                            <SelectItem value="shopper">Shopper</SelectItem>
                                            <SelectItem value="merchant">Merchant</SelectItem>
                                            <SelectItem value="admin">Admin</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium mb-1">Status</label>
                                    <Select
                                        value={formData.is_active ? 'active' : 'inactive'}
                                        onValueChange={(value) => setFormData({ ...formData, is_active: value === 'active' })}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <Button type="submit">Save Changes</Button>
                            </form>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        {/* User Info Card */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between pb-2">
                                    <CardTitle className="text-sm font-medium">Contact Info</CardTitle>
                                    <Mail className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm">{user.email}</div>
                                    {user.phone && (
                                        <div className="text-sm text-muted-foreground flex items-center gap-1 mt-1">
                                            <Phone className="h-3 w-3" />
                                            {user.phone}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between pb-2">
                                    <CardTitle className="text-sm font-medium">Role & Status</CardTitle>
                                    <Shield className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="flex gap-2">
                                        <Badge className={getRoleColor(user.role)}>
                                            {user.role}
                                        </Badge>
                                        <Badge className={getStatusColor(user.is_active)}>
                                            {user.is_active ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between pb-2">
                                    <CardTitle className="text-sm font-medium">Account Created</CardTitle>
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm">
                                        {new Date(user.created_at).toLocaleDateString()}
                                    </div>
                                    <div className="text-xs text-muted-foreground">
                                        Last updated: {new Date(user.updated_at).toLocaleDateString()}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Profile Specific Info */}
                        {user.shopper && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Shopper Profile</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-3 gap-4">
                                        <div>
                                            <div className="text-sm text-muted-foreground">Status</div>
                                            <div className="font-medium">{user.shopper.status}</div>
                                        </div>
                                        <div>
                                            <div className="text-sm text-muted-foreground">Rating</div>
                                            <div className="font-medium">{user.shopper.rating} / 5</div>
                                        </div>
                                        <div>
                                            <div className="text-sm text-muted-foreground">Total Batches</div>
                                            <div className="font-medium">{user.shopper.total_batches}</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {user.merchant && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Merchant Profile</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <div className="text-sm text-muted-foreground">Business Name</div>
                                            <div className="font-medium">{user.merchant.business_name}</div>
                                        </div>
                                        <div>
                                            <div className="text-sm text-muted-foreground">Status</div>
                                            <div className="font-medium">{user.merchant.status}</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Orders Table */}
                        {user.orders && user.orders.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Order History</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Order #</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Total</TableHead>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {user.orders.map((order) => (
                                                <TableRow key={order.id}>
                                                    <TableCell>{order.order_number}</TableCell>
                                                    <TableCell>
                                                        <Badge>{order.status}</Badge>
                                                    </TableCell>
                                                    <TableCell>${order.total.toFixed(2)}</TableCell>
                                                    <TableCell>{new Date(order.created_at).toLocaleDateString()}</TableCell>
                                                    <TableCell>
                                                        <Link href={`/admin/orders/${order.id}`}>
                                                            <Button variant="ghost" size="sm">View</Button>
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
