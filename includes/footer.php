<!-- Footer -->
<footer class="bg-[#080B10] border-t border-gray-900 text-white py-16 relative overflow-hidden mt-16">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="grid md:grid-cols-4 gap-12">
            <div class="md:col-span-2">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-[#00D4AA] rounded-xl flex items-center justify-center font-extrabold text-[#080B10] text-2xl mr-4 shadow-md">
                        N
                    </div>
                    <div>
                        <h4 class="text-2xl font-bold text-white">Nexpert.ai</h4>
                        <span class="text-xs bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 font-semibold px-2 py-0.5 rounded-full mt-1 inline-block">Trust Intelligence Platform</span>
                    </div>
                </div>
                <p class="text-gray-400 mb-8 max-w-md leading-relaxed text-sm">
                    Connecting learners with top-tier, behavioral-evidence-verified experts for real, measurable outcomes.
                </p>
            </div>
            
            <div>
                <h5 class="font-bold mb-6 text-sm uppercase tracking-wider text-gray-300 flex items-center">
                    <span class="w-2 h-2 bg-[#00D4AA] rounded-full mr-2.5"></span>
                    Explore Categories
                </h5>
                <ul class="space-y-3 text-sm">
                    <li><a href="?panel=learner&page=browse-experts&category=AI%20%26%20Technology" class="text-gray-400 hover:text-[#00D4AA] transition">
                        AI & Technology
                    </a></li>
                    <li><a href="?panel=learner&page=browse-experts&category=Leadership" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Leadership
                    </a></li>
                    <li><a href="?panel=learner&page=browse-experts&category=Career%20Growth" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Career Growth
                    </a></li>
                    <li><a href="?panel=learner&page=browse-experts&category=Entrepreneurship" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Entrepreneurship
                    </a></li>
                    <li><a href="?panel=learner&page=browse-experts&category=Product%20%26%20Strategy" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Product & Strategy
                    </a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-6 text-sm uppercase tracking-wider text-gray-300 flex items-center">
                    <span class="w-2 h-2 bg-[#00D4AA] rounded-full mr-2.5"></span>
                    Platform & Trust
                </h5>
                <ul class="space-y-3 text-sm">
                    <li><a href="index.php?page=methodology" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Trust Methodology
                    </a></li>
                    <li><a href="?panel=expert&page=apply" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Apply as an Expert
                    </a></li>
                    <li><a href="index.php?page=for-enterprise" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Enterprise Solutions
                    </a></li>
                    <li><a href="index.php?page=privacy-policy" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Privacy Policy
                    </a></li>
                    <li><a href="index.php?page=terms" class="text-gray-400 hover:text-[#00D4AA] transition">
                        Terms of Service
                    </a></li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-900 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-xs font-medium">
                    © <?php echo date('Y'); ?> Nexpert.ai. All rights reserved.
                </p>
                
                <div class="flex items-center space-x-6">
                    <div class="flex items-center text-gray-400 text-xs font-medium">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></div>
                        <span>All systems operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Auto-trigger cron jobs silently in background -->
<img src="<?php echo BASE_PATH; ?>/cron/trigger.php" style="display:none;" alt="" width="1" height="1" />
</body>
</html>
<?php
// Alternative: PHP include method (more reliable)
@include_once __DIR__ . '/../cron/trigger.php';
?>
