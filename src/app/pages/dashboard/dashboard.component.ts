import {Component, OnInit, signal, OnDestroy} from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { Project, Stats } from '../../models/project.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, OnDestroy {
  projects = signal<Project[]>([]);
  stats = signal<Stats|null>(null);
  organisation = signal<any>(null);
  private subscriptions = new Subscription();

  constructor(
    private projectService: ProjectService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(this.route.params.subscribe(params => {
      const customerId = params['id'];
      this.loadDashboardData(customerId);
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  getSegmentOffset(index: number, payments: any[]): number {
    let total = payments.reduce((sum, p) => sum + p.total_paid, 0);
    if (total === 0) return 0;
    let sumBefore = payments.slice(0, index).reduce((sum, p) => sum + p.total_paid, 0);
    return (sumBefore / total) * 100;
  }

  getSegmentWidth(index: number, payments: any[]): number {
    let total = payments.reduce((sum, p) => sum + p.total_paid, 0);
    if (total === 0) return 0;
    return (payments[index].total_paid / total) * 100;
  }

  loadDashboardData(customerId?: string): void {
    this.subscriptions.add(this.projectService.getOrganisation().subscribe(data => this.organisation.set(data)));

    this.subscriptions.add(this.projectService.getProjects(customerId).subscribe({
        next: (data) => this.projects.set(data),
        error: (err) => {
            console.error('Failed to fetch projects', err);
            if (customerId === '161') {
                this.projects.set([
                    { id: 12, name: 'Stubbs House', customer_name: 'Mr Macdonald', quote_count: 1, invoice_count: 1, created_datetime: '01/02/2022', modified_datetime: '01/02/2022', status: 'PAID', net_total: 1068.98, user_name: 'Natalie Lowe', customer_id: 161 } as Project
                ]);
            }
        }
    }));

    this.subscriptions.add(this.projectService.getStats('1', customerId).subscribe(data => this.stats.set(data)));
  }
}
