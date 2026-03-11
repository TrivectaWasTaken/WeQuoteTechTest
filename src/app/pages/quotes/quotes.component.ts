import { Component, OnDestroy, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule, Router } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { Project, Stats } from '../../models/project.model';

@Component({
  selector: 'app-quotes-page',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './quotes.component.html'
})
export class QuotesComponent implements OnInit, OnDestroy {
  customerId = signal<string | null>(null);
  projectId = signal<number | null>(null);
  quotes = signal<any[]>([]);
  project = signal<Project | null>(null);
  stats = signal<Stats | null>(null);
  private sub?: any;

  constructor(private route: ActivatedRoute, private router: Router, private projectService: ProjectService) {}

  ngOnInit(): void {
    this.sub = this.route.params.subscribe(params => {
      const cid = params['customerId'];
      const pid = +params['projectId'];
      this.customerId.set(cid);
      this.projectId.set(pid);

      if (cid) {
        this.projectService.getStats('1', cid).subscribe(data => this.stats.set(data));
      }

      this.projectService.getProject(pid).subscribe(p => this.project.set(p));
      this.projectService.getQuotes(String(pid)).subscribe(list => this.quotes.set(list));
    });
  }

  ngOnDestroy(): void {
    if (this.sub) { this.sub.unsubscribe(); }
  }

  backToHub(): void {
    const cid = this.customerId();
    const pid = this.projectId();
    if (cid && pid) {
      this.router.navigate(['/project-hub', cid, pid]);
    }
  }

  getPaidWidth(): number {
    const p = this.project();
    if (!p || !p.net_total) return 0;
    return (p.paid_total / p.net_total) * 100;
  }

  getOutstandingWidth(): number {
    const p = this.project();
    if (!p || !p.net_total) return 0;
    return ((p.outstanding_total ?? 0) / p.net_total) * 100;
  }
}
